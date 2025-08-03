<?php

use App\Http\Controllers\Api\v1\AuthController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

// Route Ping
Route::get('/ping', function () {
    return response()->json(['message' => 'pong']);
});

// API V1 Routes
Route::prefix('v1')->group(function () {
    // Auth Routes com Rate Limiting
    Route::prefix('auth')->group(function () {
        // Rotas públicas com rate limiting mais restritivo
        Route::middleware(['throttle:5,1'])->group(function () {
            Route::post('register', [AuthController::class, 'register']);
            Route::post('login', [AuthController::class, 'login']);
        });
        
        // Rotas autenticadas com rate limiting padrão
        Route::middleware(['jwt.auth', 'throttle:60,1'])->group(function () {
            Route::post('refresh', [AuthController::class, 'refresh']);
            Route::post('logout', [AuthController::class, 'logout']);
            Route::get('me', [AuthController::class, 'me']);
        });
    });
});
