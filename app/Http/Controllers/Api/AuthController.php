<?php

namespace App\Http\Controllers\Api;

use App\Http\Traits\ApiResponse;
use App\Models\Otp;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Twilio\Rest\Client;
use Tymon\JWTAuth\Facades\JWTAuth;

class AuthController extends ApiBaseController
{
    use ApiResponse;

    /**
     * Register a new user
     */
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:client,vehicle_owner,driver',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        // Assign role using Spatie
        $user->assignRole($request->role);

        $token = JWTAuth::fromUser($user);

        return $this->sendResponse([
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
        ], 201);
    }

    /**
     * Login user with Passport token (email/phone + OTP)
     */
    public function login(Request $request)
    {
        $request->validate([
            'login_type' => 'required|in:email,phone',
            'email' => 'required_if:login_type,email|email|exists:users,email,deleted_at,NULL',
            'phone' => 'required_if:login_type,phone|exists:users,phone,deleted_at,NULL',
            'otp' => 'required|string|size:6',
            'role' => 'required|in:client,vehicle_owner,driver,admin',
            'deviceId' => 'nullable|string',
            'deviceToken' => 'nullable|string',
            'deviceName' => 'nullable|string',
            'deviceType' => 'nullable|string',
            'fcmToken' => 'nullable|string',
        ]);

        DB::beginTransaction();

        // Find user by email or phone
        $user = null;
        if ($request->login_type === 'email') {
            $user = User::where('email', $request->email)->first();
        } else {
            $user = User::where('phone', $request->phone)->first();
        }

        if (! $user) {
            return response()->json(['message' => 'User not found'], 404);
        }

        // Check OTP validity
        $otpQuery = $request->login_type === 'email'
            ? Otp::where(['user_id' => $user->id, 'otp' => $request->otp, 'is_used' => false, 'type' => 'email_verification'])
            : Otp::where(['user_id' => $user->id, 'otp' => $request->otp, 'is_used' => false, 'type' => 'phone_verification']);
        $otpValid = $otpQuery->where('expires_at', '>', now())->first();

        if (! $otpValid) {
            return response()->json(['message' => 'Invalid or expired OTP'], 401);
        }

        // Check user status and role
        if ($user->status == 'rejected') {
            return response()->json(['message' => 'Application Rejected'], 401);
        }
        if ($user->status != 'active') {
            return response()->json(['message' => 'Your account is not active. Please contact the administrator.'], 403);
        }
        if (! $user->hasRole($request->role)) {
            return response()->json(['message' => 'Selected role is not valid for this user'], 401);
        }

        // Mark OTP as used
        $otpValid->markAsUsed();

        // Update device info
        $user->device_id = $request->deviceId;
        $user->device_token = $request->deviceToken;
        $user->device_name = $request->deviceName;
        $user->device_type = $request->deviceType;
        $user->fcm_token = $request->fcmToken;
        $user->save();

        // Issue JWT token
        $token = JWTAuth::fromUser($user);

        DB::commit();

        return $this->sendResponse([
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ], 200);
    }

    /**
     * Send OTP to user's phone
     */
    public function sendOtp(Request $request)
    {
        $request->validate([
            'login_type' => 'required|in:email,phone',
            'email' => 'required_if:login_type,email|email|exists:users,email,deleted_at,NULL',
            'phone' => 'required_if:login_type,phone|exists:users,phone,deleted_at,NULL',
            'role' => 'required|in:client,vehicle_owner,driver,admin',
        ]);

        $otpCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $user = null;
        $otp = null;

        if ($request->login_type === 'email') {
            $user = User::where('email', $request->email)->first();
            // Invalidate any existing unused OTPs for this email
            Otp::where('type', 'email_verification')
                ->where('is_used', false)
                ->where('expires_at', '>', now())
                ->where('email', $request->email)
                ->update(['is_used' => true]);
            $otp = Otp::create([
                'user_id' => $user?->id,
                'email' => $request->email,
                'otp' => $otpCode,
                'type' => 'email_verification',
                'expires_at' => now()->addMinutes(10),
                'ip_address' => $request->ip(),
            ]);
            if (! $user) {
                $user = User::create([
                    'email' => $request->email,
                ]);
                $user->assignRole($request->role);
                $otp->update(['user_id' => $user->id]);
            }
            // TODO: Send OTP via email service
        } else {
            $user = User::where('phone', $request->phone)->first();
            Otp::forPhone($request->phone)
                ->valid()
                ->update(['is_used' => true]);
            $otp = Otp::create([
                'user_id' => $user?->id,
                'phone' => $request->phone,
                'otp' => $otpCode,
                'type' => 'phone_verification',
                'expires_at' => now()->addMinutes(10),
                'ip_address' => $request->ip(),
            ]);
            if (! $user) {
                $user = User::create([
                    'phone' => $request->phone,
                ]);
                $user->assignRole($request->role);
                $otp->update(['user_id' => $user->id]);
            }
            // Send OTP via Twilio SMS
            try {
                $sid = env('TWILIO_SID');
                $token = env('TWILIO_AUTH_TOKEN');
                $twilio = new Client($sid, $token);

                $verification_check = $twilio->verify->v2->services(env('TWILIO_VERIFY_SERVICE_SID'))
                    ->verificationChecks
                    ->create([
                        'to' => '+14472841344',
                        'code' => '123456',
                    ]
                    );

                dd($verification_check);
                $twilio->messages->create(
                    $request->phone,
                    [
                        'from' => $from,
                        'body' => "Your HeavyRent OTP is: $otpCode",
                    ]
                );
            } catch (\Exception $e) {
                return $this->sendResponse([], 'Failed to send OTP via SMS', 500);
            }
        }

        return $this->sendResponse($otp, 'OTP sent successfully', 200);
    }

    /**
     * Verify OTP and return JWT token
     */
    public function verifyOtp(Request $request)
    {
        $request->validate([
            'login_type' => 'required|in:email,phone',
            'email' => 'required_if:login_type,email|email',
            'phone' => 'required_if:login_type,phone',
            'otp' => 'required|string|size:6',
            'role' => 'required|in:client,vehicle_owner,driver,admin',
        ]);

        $otp = null;
        $user = null;
        if ($request->login_type === 'email') {
            $otp = Otp::where('type', 'email_verification')
                ->where('email', $request->email)
                ->where('otp', $request->otp)
                ->valid()
                ->first();
            $user = $otp?->user ?? User::where('email', $request->email)->first();
        } else {
            $otp = Otp::forPhone($request->phone)
                ->where('otp', $request->otp)
                ->valid()
                ->first();
            $user = $otp?->user ?? User::where('phone', $request->phone)->first();
        }

        if (! $otp) {
            if ($request->login_type === 'email') {
                Otp::where('type', 'email_verification')
                    ->where('email', $request->email)
                    ->where('otp', $request->otp)
                    ->where('expires_at', '>', now())
                    ->first()?->incrementAttempts();
            } else {
                Otp::forPhone($request->phone)
                    ->where('otp', $request->otp)
                    ->where('expires_at', '>', now())
                    ->first()?->incrementAttempts();
            }

            return $this->sendResponse([], 'Invalid or expired OTP', 401);
        }

        if ($otp->attempts >= 5) {
            return $this->sendResponse([], 'Too many attempts. Please request a new OTP.', 429);
        }

        if (! $user) {
            $userData = [];
            if ($request->login_type === 'email') {
                $userData['email'] = $request->email;
            } else {
                $userData['phone'] = $request->phone;
            }
            $user = User::create($userData);
            $user->assignRole($request->role);
        } elseif (! $user->hasRole($request->role)) {
            $user->assignRole($request->role);
        }

        $otp->markAsUsed();

        $user->update(['verified_at' => now()]);

        $token = JWTAuth::fromUser($user);

        return $this->sendResponse([
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ], 'OTP verified successfully', 200);
    }

    /*
     * write a function to social login a user with passport token for an api request
     */
    public function socialLogin(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'socialLoginName' => 'required',
            'socialLoginToken' => 'required',
            'role' => 'required|in:client,vehicle_owner,driver,admin',
            'deviceId' => 'nullable|string',
            'deviceToken' => 'nullable|string',
            'deviceName' => 'nullable|string',
            'deviceType' => 'nullable|string',
            'fcmToken' => 'nullable|string',
        ]);

        $user = User::where('email', $request->email)->first();
        if (! $user) {
            $user = User::create([
                'email' => $request->email,
                'name' => $request->fullName ?? $request->socialLoginName,
                'verified_at' => $objUser->verified_at ?? Carbon::now(),
                'status' => 'active',
            ]);
            $user->assignRole($request->role);
        } elseif (! $user->hasRole($request->role)) {
            $user->assignRole($request->role);
        }

        // Check user status
        if ($user->status == 'rejected') {
            return $this->sendResponse([], 'Application Rejected', 401);
        }
        if ($user->status != 'active') {
            return $this->sendResponse([], 'Your account is not active. Please contact the administrator.', 403);
        }

        // Update device info
        $user->social_login_name = $request->socialLoginName;
        $user->social_login_token = $request->socialLoginToken;
        $user->device_id = $request->deviceId;
        $user->device_token = $request->deviceToken;
        $user->device_name = $request->deviceName;
        $user->device_type = $request->deviceType;
        $user->fcm_token = $request->fcmToken;
        $user->save();

        // Issue JWT token
        $token = JWTAuth::fromUser($user);

        return $this->sendResponse([
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ], 'Social login successful');
    }

    /**
     * Google OAuth login (stub)
     */
    public function google(Request $request)
    {
        // TODO: Implement Google OAuth login
        return $this->sendResponse([], 'Google login not implemented yet.', 501);
    }

    /**
     * Get authenticated user
     */
    public function me()
    {
        return $this->sendResponse([
            'user' => auth()->user(),
        ], 'Authenticated user retrieved successfully');
    }

    /**
     * Refresh JWT token
     */
    public function refresh()
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());

            return $this->sendResponse([
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ], 'Token refreshed successfully');
        } catch (\Exception $e) {
            return $this->sendResponse([], 'Could not refresh token', 401);
        }
    }

    /**
     * Logout user
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return $this->sendResponse([], 'Successfully logged out');
        } catch (\Exception $e) {
            return $this->sendResponse([], 'Could not logout', 500);
        }
    }
}
