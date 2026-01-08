<?php

namespace App\Services;

use App\Models\Credential;
use App\Mail\OtpMail;
use Carbon\Carbon;
use Illuminate\Support\Facades\Mail;

class OtpService
{
    private int $otpLength;
    private int $otpExpiryMin;
    private int $otpExpiryMax;

    public function __construct()
    {
        $this->otpLength = config('otp.length', 4);
        $this->otpExpiryMin = config('otp.expiry_min', 5);
        $this->otpExpiryMax = config('otp.expiry_max', 10);
    }

    /**
     * Generate numeric OTP
     */
    public function generateOtp(): string
    {
        $min = pow(10, $this->otpLength - 1);
        $max = pow(10, $this->otpLength) - 1;

        return (string) random_int($min, $max);
    }

    /**
     * Generate and send OTP to user
     */
    public function sendOtp(Credential $credential): void
    {
        $otp = $this->generateOtp();
        $expiryMinutes = random_int($this->otpExpiryMin, $this->otpExpiryMax);

        // Store OTP in database
        $credential->update([
            'otp' => $otp,
            'otp_expiry' => Carbon::now()->addMinutes($expiryMinutes),
        ]);

        // Send email
        Mail::to($credential->email)->send(new OtpMail($otp, $expiryMinutes));
    }

    /**
     * Verify OTP
     */
    public function verifyOtp(Credential $credential, string $otp): bool
    {
        if (!$credential->hasValidOtp($otp)) {
            return false;
        }

        // Clear OTP after successful verification
        $credential->clearOtp();

        return true;
    }

    /**
     * Check if can resend OTP (rate limiting)
     */
    public function canResendOtp(Credential $credential): bool
    {
        if (!$credential->otp_expiry) {
            return true;
        }

        // Allow resend only if OTP has expired or 1 minute has passed
        $oneMinuteAgo = Carbon::now()->subMinute();
        return Carbon::now()->greaterThan($credential->otp_expiry) ||
               $credential->updated_at->lessThan($oneMinuteAgo);
    }
}
