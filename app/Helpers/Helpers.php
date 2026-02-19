<?php

use App\Models\Otp;
use App\Models\User;
use Carbon\Carbon;

if (! function_exists('sendEmail')) {
    function sendEmail($to, $subject, $view, $data)
    {
        return Mail::to($to)->send(new SendMail($data, $view, $subject));
    }
}

if (! function_exists('sendOtp')) {
    /* Send otp to email */
    function sendOtp($email, $type)
    {
        $otp = rand(1000, 9999);  // 4-digit OTP
        $expiresAt = Carbon::now()->addMinutes(10);  // Valid for 10 minutes

        // Store OTP
        Otp::updateOrCreate([
            'email' => $email,
        ], [
            'email' => $email,
            'otp' => $otp,
            'expires_at' => $expiresAt,
        ]);

        switch ($type) {
            case 'forgot_password':
                $subject = 'Reset Password OTP';
                $messageText = '<p style="color:#506b62">We have received a request to reset your account password. As part of our enhanced security measures, we have implemented a one-time password (OTP) <b>'.$otp.'</b> verification process to ensure the safety of your account.</p>';
                break;
            case 'resend_otp':
                $subject = 'Resent OTP';
                $messageText = '<p style="color:#506b62">Your OTP for verification is <b>'.$otp.'</b>. Please use this OTP to complete your verification process.</p>';
                break;
            case 'verify_email':
                $subject = 'Email Verification';
                $messageText = '<p style="color:#506b62">Thank you for signing up! Please use the OTP <b>'.$otp.'</b> to verify your email address and activate your account.</p>';
                break;
            default:
                $subject = 'OTP Verification';
                $messageText = '<p style="color:#506b62">Your OTP for verification is <b>'.$otp.'</b>. Please use this OTP to complete your verification process.</p>';
                break;
        }

        $user = User::where('email', $email)->first();
        $data = [
            'email' => $email,
            'otp' => $otp,
            'title' => $subject,
            'messageText' => $messageText,
            'otpExpiry' => 10, // OTP expiry time in minutes
            'userName' => $user->full_name,
            'actionUrl' => route('login'),
            'type' => $type, // e.g., 'otp-onboarding', 'otp-resend', etc.

        ];

        // Send OTP via email
        return sendEmail($email, $subject, 'emails.common', $data);
    }
}
