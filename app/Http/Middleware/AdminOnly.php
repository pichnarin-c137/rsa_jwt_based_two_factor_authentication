<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class AdminOnly
{
    public function handle(Request $request, Closure $next): Response
    {
        $role = $request->get('auth_role');

        if (!$role || strtolower($role) !== 'admin') {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden - Admin access required'
            ], 403);
        }

        return $next($request);
    }
}
