<?php

namespace App\Http\Controllers;

use App\Http\Requests\RegisterRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Exception;

class UserController extends Controller
{
    public function __construct(
        private UserService $userService
    ) {}

    /**
     * Get logged-in user profile
     */
    public function getProfile(Request $request): JsonResponse
    {
        try {
            $userId = $request->get('auth_user_id');
            $profile = $this->userService->getUserProfile($userId);

            return response()->json([
                'success' => true,
                'data' => $profile
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => $e->getMessage()
            ], 404);
        }
    }

    /**
     * Create new user (admin only)
     */
    public function createUser(RegisterRequest $request): JsonResponse
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
                'message' => 'User created successfully. OTP sent to email.',
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
}
