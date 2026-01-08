<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
*/

// Public routes (no authentication required)
Route::prefix('auth')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login']);
    Route::post('/verify-otp', [AuthController::class, 'verifyOtp']);
    Route::post('/refresh-token', [AuthController::class, 'refreshToken']);
});

// Protected routes (JWT required)
Route::middleware(['jwt.auth'])->group(function () {

    // Logout
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // User profile
    Route::get('/get-profile', [UserController::class, 'getProfile']);

    // Admin-only routes
    Route::middleware(['admin.only'])->group(function () {
        Route::post('/create-user', [UserController::class, 'createUser']);
    });
});
