<?php

namespace App\Services;

use App\Models\User;
use App\Models\Credential;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\DB;
use Exception;

class AuthService
{
    public function __construct(
        private JwtService $jwtService,
        private OtpService $otpService
    ) {}

    /**
     * Step 1: Authenticate user with username/email and password
     * Returns credential if successful, triggers OTP send
     */
    public function initiateLogin(string $identifier, string $password): Credential
    {
        // Find credential by email or username
        $credential = Credential::where('email', $identifier)
            ->orWhere('username', $identifier)
            ->with('user.role')
            ->first();

        if (!$credential) {
            throw new Exception('Invalid credentials');
        }

        // Verify password
        if (!Hash::check($password, $credential->password)) {
            throw new Exception('Invalid credentials');
        }

        // Check if user is suspended
        if ($credential->user->isSuspended()) {
            throw new Exception('Account is suspended');
        }

        // Generate and send OTP
        $this->otpService->sendOtp($credential);

        return $credential;
    }

    /**
     * Step 2: Verify OTP and issue tokens
     */
    public function verifyOtpAndIssueTokens(string $identifier, string $otp): array
    {
        $credential = Credential::where('email', $identifier)
            ->orWhere('username', $identifier)
            ->with('user.role')
            ->first();

        if (!$credential) {
            throw new Exception('Invalid credentials');
        }

        // Verify OTP
        if (!$this->otpService->verifyOtp($credential, $otp)) {
            throw new Exception('Invalid or expired OTP');
        }

        $user = $credential->user;

        // Generate tokens
        $accessToken = $this->jwtService->generateAccessToken($user);
        $refreshToken = $this->jwtService->generateRefreshToken($user);

        return [
            'access_token' => $accessToken,
            'refresh_token' => $refreshToken,
            'token_type' => 'Bearer',
            'expires_in' => config('jwt.access_token_expiry', 1440) * 60,
            'user' => $this->formatUserResponse($user),
        ];
    }

    /**
     * Logout user by revoking refresh token
     */
    public function logout(string $refreshToken): void
    {
        $this->jwtService->revokeRefreshToken($refreshToken);
    }

    /**
     * Format user response (exclude sensitive data)
     */
    private function formatUserResponse(User $user): array
    {
        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'dob' => $user->dob->format('Y-m-d'),
            'address' => $user->address,
            'gender' => $user->gender,
            'nationality' => $user->nationality,
            'role' => $user->role->role,
            'email' => $user->credential->email,
            'username' => $user->credential->username,
            'phone_number' => $user->credential->phone_number,
        ];
    }
}
