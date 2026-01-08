<?php

namespace App\Services;

use App\Models\User;
use App\Models\RefreshToken;
use Carbon\Carbon;
use Exception;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;

class JwtService
{
    private string $privateKey;
    private string $publicKey;
    private int $accessTokenExpiry;
    private int $refreshTokenExpiry;

    public function __construct()
    {
        $this->privateKey = file_get_contents(storage_path(config('jwt.private_key_path', 'keys/jwt_private.pem')));
        $this->publicKey = file_get_contents(storage_path(config('jwt.public_key_path', 'keys/jwt_public.pem')));
        $this->accessTokenExpiry = config('jwt.access_token_expiry', 1440); // minutes
        $this->refreshTokenExpiry = config('jwt.refresh_token_expiry', 43200); // minutes
    }

    /**
     * Generate access token (short-lived)
     */
    public function generateAccessToken(User $user): string
    {
        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->id,
            'user_id' => $user->id,
            'role' => $user->role->role,
            'iat' => time(),
            'exp' => time() + ($this->accessTokenExpiry * 60),
            'type' => 'access',
        ];

        return JWT::encode($payload, $this->privateKey, 'RS256');
    }

    /**
     * Generate refresh token (long-lived) and store in DB
     */
    public function generateRefreshToken(User $user): string
    {
        $payload = [
            'iss' => config('app.url'),
            'sub' => $user->id,
            'user_id' => $user->id,
            'iat' => time(),
            'exp' => time() + ($this->refreshTokenExpiry * 60),
            'type' => 'refresh',
            'jti' => bin2hex(random_bytes(32)), // Unique token ID
        ];

        $token = JWT::encode($payload, $this->privateKey, 'RS256');

        // Store in database
        RefreshToken::create([
            'user_id' => $user->id,
            'token' => $token,
            'expires_at' => Carbon::now()->addMinutes($this->refreshTokenExpiry),
        ]);

        return $token;
    }

    /**
     * Validate and decode token
     */
    public function validateToken(string $token): object
    {
        try {
            return JWT::decode($token, new Key($this->publicKey, 'RS256'));
        } catch (Exception $e) {
            throw new Exception('Invalid or expired token: ' . $e->getMessage());
        }
    }

    /**
     * Refresh access token using refresh token
     */
    public function refreshAccessToken(string $refreshToken): array
    {
        // Validate refresh token
        $decoded = $this->validateToken($refreshToken);

        if ($decoded->type !== 'refresh') {
            throw new Exception('Invalid token type');
        }

        // Check if token exists in DB and is valid
        $storedToken = RefreshToken::where('token', $refreshToken)
            ->where('user_id', $decoded->user_id)
            ->first();

        if (!$storedToken || !$storedToken->isValid()) {
            throw new Exception('Refresh token is invalid or revoked');
        }

        // Get user
        $user = User::with('role')->findOrFail($decoded->user_id);

        if ($user->isSuspended()) {
            throw new Exception('User account is suspended');
        }

        // Generate new access token
        $newAccessToken = $this->generateAccessToken($user);

        return [
            'access_token' => $newAccessToken,
            'token_type' => 'Bearer',
            'expires_in' => $this->accessTokenExpiry * 60,
        ];
    }

    /**
     * Revoke refresh token
     */
    public function revokeRefreshToken(string $token): void
    {
        $refreshToken = RefreshToken::where('token', $token)->first();

        if ($refreshToken) {
            $refreshToken->revoke();
        }
    }

    /**
     * Revoke all user's refresh tokens
     */
    public function revokeAllUserTokens(string $userId): void
    {
        RefreshToken::where('user_id', $userId)
            ->where('is_revoked', false)
            ->update(['is_revoked' => true]);
    }
}
