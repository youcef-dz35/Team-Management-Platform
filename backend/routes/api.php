<?php

use App\Http\Controllers\Api\V1\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/health', function () {
    return response()->json([
        'status' => 'healthy',
        'service' => 'backend-api',
        'timestamp' => now()->toIso8601String(),
    ]);
});

// API V1 Routes
Route::prefix('v1')->group(function () {

    // Authentication Routes (Public)
    Route::prefix('auth')->group(function () {
        Route::post('/login', [AuthController::class, 'login'])
            ->middleware('throttle:5,1'); // 5 attempts per minute

        // Protected Auth Routes
        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::get('/me', [AuthController::class, 'me']);
        });
    });

    // Protected API Routes
    Route::middleware('auth:sanctum')->group(function () {
        // User routes will go here
        // Project routes will go here
        // Department routes will go here
    });
});

// Legacy route (can be removed later)
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});
