<?php

namespace App\Http\Middleware;

use App\Services\JwtService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Exception;

class JwtAuthenticate
{
    public function __construct(
        private JwtService $jwtService
    ) {}

    public function handle(Request $request, Closure $next): Response
    {
        $token = $this->extractToken($request);

        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - No token provided'
            ], 401);
        }

        try {
            $decoded = $this->jwtService->validateToken($token);

            // Ensure it's an access token
            if ($decoded->type !== 'access') {
                throw new Exception('Invalid token type');
            }

            // Attach user info to request
            $request->merge([
                'auth_user_id' => $decoded->user_id,
                'auth_role' => $decoded->role,
            ]);

        } catch (Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthorized - ' . $e->getMessage()
            ], 401);
        }

        return $next($request);
    }

    private function extractToken(Request $request): ?string
    {
        $header = $request->header('Authorization', '');

        if (preg_match('/Bearer\s+(.*)$/i', $header, $matches)) {
            return $matches[1];
        }

        return null;
    }
}
