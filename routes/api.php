<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\Auth\AuthController;    
use App\Http\Controllers\Api\V1\AssetController;

Route::prefix('v1')->group(function () {
    // auth
    Route::prefix('auth')->group(function () {
        Route::post('/register', [AuthController::class, 'register']);
        Route::post('/login', [AuthController::class, 'login']);

        Route::middleware('auth:sanctum')->group(function () {
            Route::post('/logout', [AuthController::class, 'logout']);
            Route::post('/refresh', [AuthController::class, 'refresh']);
            Route::get('/me', [AuthController::class, 'me']);
        });
    });

    // assets
    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/assets', [AssetController::class, 'index']);
        Route::get('/assets/{asset}', [AssetController::class, 'show']);
    });
});
