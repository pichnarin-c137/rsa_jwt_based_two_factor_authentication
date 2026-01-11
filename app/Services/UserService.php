<?php

namespace App\Services;

use App\Models\User;
use App\Models\Credential;
use App\Models\Role;
use App\Exceptions\RoleNotFoundException;
use App\Exceptions\UserNotFoundException;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class UserService
{
    public function __construct(
        private OtpService $otpService
    ) {}

    /**
     * Create new user (admin only)
     */
    public function createUser(array $userData, array $credentialData): User
    {
        return DB::transaction(function () use ($userData, $credentialData) {
            // Get role
            $role = Role::where('role', $userData['role'] ?? 'user')->first();

            if (!$role) {
                throw new RoleNotFoundException("Role '{$userData['role']}' not found");
            }

            // Create user
            $user = User::create([
                'role_id' => $role->id,
                'first_name' => $userData['first_name'],
                'last_name' => $userData['last_name'],
                'dob' => $userData['dob'],
                'address' => $userData['address'],
                'gender' => $userData['gender'],
                'nationality' => $userData['nationality'],
            ]);

            // Create credentials
            $credential = Credential::create([
                'user_id' => $user->id,
                'email' => $credentialData['email'],
                'username' => $credentialData['username'],
                'phone_number' => $credentialData['phone_number'],
                'password' => Hash::make($credentialData['password']),
            ]);

            // Send OTP for email verification
            $this->otpService->sendOtp($credential);

            return $user->load(['role', 'credential']);
        });
    }

    /**
     * Get user profile
     */
    public function getUserProfile(string $userId): array
    {
        $user = User::with(['role', 'credential'])->find($userId);

        if (!$user) {
            throw new UserNotFoundException('User not found', 0, null, ['user_id' => $userId]);
        }

        return [
            'id' => $user->id,
            'first_name' => $user->first_name,
            'last_name' => $user->last_name,
            'full_name' => $user->full_name,
            'dob' => $user->dob->format('Y-m-d'),
            'address' => $user->address,
            'gender' => $user->gender,
            'nationality' => $user->nationality,
            'is_suspended' => $user->is_suspended,
            'role' => $user->role->role,
            'email' => $user->credential->email,
            'username' => $user->credential->username,
            'phone_number' => $user->credential->phone_number,
            'created_at' => $user->created_at->toIso8601String(),
        ];
    }

    /**
     * Suspend/Unsuspend user
     */
    public function toggleSuspension(string $userId): User
    {
        $user = User::find($userId);

        if (!$user) {
            throw new UserNotFoundException('User not found', 0, null, ['user_id' => $userId]);
        }

        $user->update(['is_suspended' => !$user->is_suspended]);

        return $user;
    }
}
