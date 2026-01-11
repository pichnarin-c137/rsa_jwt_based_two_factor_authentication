<?php

namespace App\Http\Controllers;

use App\Http\Requests\AdminCreateUserRequest;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

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
        $userId = $request->get('auth_user_id');
        $profile = $this->userService->getUserProfile($userId);

        return response()->json([
            'success' => true,
            'data' => $profile
        ]);
    }

    /**
     * Create new user (admin only - can create users with any role)
     */
    public function createUser(AdminCreateUserRequest $request): JsonResponse
    {
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
    }
}
