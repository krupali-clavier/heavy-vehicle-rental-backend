<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Api\ApiBaseController;
use App\Http\Traits\ApiResponse;
use App\Models\Otp;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
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
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'phone' => 'required|string|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'role' => 'required|in:client,vehicle_owner,driver',
        ]);

        if ($validator->fails()) {
            return $this->sendError('Validation error', $validator->errors(), 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'phone' => $request->phone,
            'password' => Hash::make($request->password),
        ]);

        // Assign role using Spatie
        $user->assignRole($request->role);

        $token = JWTAuth::fromUser($user);

        return $this->sendSuccess([
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
        ], 'User registered successfully', 201);
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
            return $this->sendError('Validation error', $validator->errors(), 422);
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

        return response()->json([
            'success' => true,
            'message' => 'Login Successfully',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ],
        ]);
    }

    /**
     * Send OTP to user's phone
     */
    /*
     * write a function to resend otp
     */
    // public function resendOtp(Request $request)
    // {
    //     $request->validate(['email' => 'required|email|exists:users,email,deleted_at,NULL']);

    //     if (sendOtp($request->email, 'resend_otp')) {
    //         return sendResponse(200, 'OTP sent successfully');
    //     }

    //     return sendResponse(
    //         500,
    //         'Something went wrong !',
    //     );
    // }

    public function sendOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login_type' => 'required|in:email,phone',
            'email' => 'required_if:login_type,email|email|exists:users,email,deleted_at,NULL',
            'phone' => 'required_if:login_type,phone|exists:users,phone,deleted_at,NULL',
            'role' => 'required|in:client,vehicle_owner,driver,admin',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $otpCode = str_pad(rand(0, 999999), 6, '0', STR_PAD_LEFT);
        $user = null;
        $otp = null;

        if ($request->login_type === 'email') {
            $user = User::where('email', $request->email)->first();
            // Invalidate any existing unused OTPs for this email
            Otp::where('type', 'email_verification')
                ->where('is_used', false)
                ->where('expires_at', '>', now())
                return $this->sendError('Failed to send OTP via SMS', ['exception' => $e->getMessage()], 500);
                'otp' => $otpCode,
                'type' => 'email_verification',
                'expires_at' => now()->addMinutes(10),
        return $this->sendSuccess([
            'otp' => $otpCode, // Remove in production
            'expires_in' => 600,
        ], 'OTP sent successfully');
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
            return $this->sendError('Validation error', $validator->errors(), 422);
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
                return response()->json([
                    'success' => false,
                    'message' => 'Failed to send OTP via SMS',
                    'error' => $e->getMessage(),
                ], 500);
            }
        }

            return $this->sendError('Invalid or expired OTP', [], 401);
                'otp' => $otpCode, // Remove in production
                'expires_in' => 600,
            ],
            return $this->sendError('Too many attempts. Please request a new OTP.', [], 429);
     * Verify OTP and return JWT token
     */
    public function verifyOtp(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'login_type' => 'required|in:email,phone',
            'email' => 'required_if:login_type,email|email',
            'phone' => 'required_if:login_type,phone',
            'otp' => 'required|string|size:6',
            'role' => 'required|in:client,vehicle_owner,driver,admin',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'success' => false,
                'errors' => $validator->errors(),
            ], 422);
        }

        $otp = null;
        $user = null;
        if ($request->login_type === 'email') {
            $otp = Otp::where('type', 'email_verification')
                ->where('email', $request->email)
                ->where('otp', $request->otp)
        return $this->sendSuccess([
            'user' => $user,
            'token' => $token,
            'token_type' => 'bearer',
            'expires_in' => JWTAuth::factory()->getTTL() * 60,
        ], 'OTP verified successfully');

        if (! $otp) {
            if ($request->login_type === 'email') {
                Otp::where('type', 'email_verification')
                    ->where('email', $request->email)
                    ->where('otp', $request->otp)
                    ->where('expires_at', '>', now())
                    ->first()?->incrementAttempts();
        return $this->sendError('Google login not implemented yet.', [], 501);
                    ->first()?->incrementAttempts();
            }

            return response()->json([
                'success' => false,
                'message' => 'Invalid or expired OTP',
            ], 401);
        return $this->sendSuccess([
            'user' => auth()->user(),
        ], 'User details fetched successfully');
            ], 429);
        }

        if (! $user) {
            $userData = [];
            if ($request->login_type === 'email') {
                $userData['email'] = $request->email;
            } else {
                $userData['phone'] = $request->phone;
            }
            return $this->sendSuccess([
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            ], 'Token refreshed successfully');
        if ($request->login_type === 'email') {
            return $this->sendError('Could not refresh token', [], 401);

        $token = JWTAuth::fromUser($user);

        return response()->json([
            'success' => true,
            'message' => 'OTP verified successfully',
            'data' => [
                'user' => $user,
                'token' => $token,
                'token_type' => 'bearer',
                'expires_in' => JWTAuth::factory()->getTTL() * 60,
            return $this->sendSuccess([], 'Successfully logged out');
    /**
            return $this->sendError('Could not logout', [], 500);
        // TODO: Implement Google OAuth login
        return response()->json([
            'success' => false,
            'message' => 'Google login not implemented yet.',
        ], 501);
    }

    /**
     * Get authenticated user
     */
    public function me()
    {
        return response()->json([
            'success' => true,
            'data' => [
                'user' => auth()->user(),
            ],
        ]);
    }

    /**
     * Refresh JWT token
     */
    public function refresh()
    {
        try {
            $token = JWTAuth::refresh(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'data' => [
                    'token' => $token,
                    'token_type' => 'bearer',
                    'expires_in' => JWTAuth::factory()->getTTL() * 60,
                ],
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not refresh token',
            ], 401);
        }
    }

    /**
     * Logout user
     */
    public function logout()
    {
        try {
            JWTAuth::invalidate(JWTAuth::getToken());

            return response()->json([
                'success' => true,
                'message' => 'Successfully logged out',
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Could not logout',
            ], 500);
        }
    }
}
