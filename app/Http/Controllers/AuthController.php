<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Http\Requests\LoginRequest;
use App\Http\Requests\VerifyOtpRequest;
use App\Http\Requests\RefreshTokenRequest;
use App\Services\AuthService;
use App\Services\UserService;
use App\Services\JwtService;
use Illuminate\Http\JsonResponse;
use Exception;

class AuthController extends Controller
{
    public function __construct(
        private AuthService $authService,
        private UserService $userService,
        private JwtService $jwtService
    ) {}

    /**
     * Register new user (admin creates account)
     */
    public function register(RegisterRequest $request): JsonResponse
    {
        try {
            $userData = $request->only([
                'first_name', 'last_name', 'dob', 'address', 'gender', 'nationality', 'role'
            ]);

            $credentialData = $request->only([
                'email', 'username', 'phone_number', 'password'
            ]);

            $user = $this->userService->createUser($userData, $credentialData);

            return response()->json([
                'success' => true,
                'message' => 'User registered successfully. OTP sent to email.',
                'data' => [
                    'user_id' => $user->id,
                    'email' => $user->credential->email,
                ]
            ], 201);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }

    /**
     * Step 1: Login with username/email + password
     */
    public function login(LoginRequest $request): JsonResponse
    {
        try {
            $credential = $this->authService->initiateLogin(
                $request->identifier,
                $request->password
            );

            return response()->json([
                'success' => true,
                'message' => 'OTP sent to your email',
                'data' => [
                    'email' => $credential->email,
                    'next_step' => 'verify_otp'
                ]
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Step 2: Verify OTP and issue tokens
     */
    public function verifyOtp(VerifyOtpRequest $request): JsonResponse
    {
        try {
            $tokens = $this->authService->verifyOtpAndIssueTokens(
                $request->identifier,
                $request->otp
            );

            return response()->json([
                'success' => true,
                'message' => 'Login successful',
                'data' => $tokens
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Refresh access token
     */
    public function refreshToken(RefreshTokenRequest $request): JsonResponse
    {
        try {
            $tokens = $this->jwtService->refreshAccessToken($request->refresh_token);

            return response()->json([
                'success' => true,
                'message' => 'Token refreshed successfully',
                'data' => $tokens
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 401);
        }
    }

    /**
     * Logout (revoke refresh token)
     */
    public function logout(RefreshTokenRequest $request): JsonResponse
    {
        try {
            $this->authService->logout($request->refresh_token);

            return response()->json([
                'success' => true,
                'message' => 'Logged out successfully'
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 400);
        }
    }
}
