<?php

use Illuminate\Support\Facades\Route;

use App\Http\Controllers\Api\V1\Auth\AuthController;    
use App\Http\Controllers\Api\V1\AssetController;
use App\Http\Controllers\Api\V1\PositionController;
use App\Http\Controllers\Api\V1\TransactionController;
use App\Http\Controllers\Api\V1\MarketDataController;
use App\Http\Controllers\Api\V1\PortfolioController;

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

    Route::middleware('auth:sanctum')->group(function () {
        // assets
        Route::get('/assets', [AssetController::class, 'index']);
        Route::get('/assets/{asset}', [AssetController::class, 'show']);

        // positions
        Route::get('/positions', [PositionController::class, 'index']);
        Route::get('/positions/{position}', [PositionController::class, 'show']);
        Route::delete('/positions/{position}', [PositionController::class, 'destroy']);

        // transactions
        Route::get('/transactions', [TransactionController::class, 'index']);
        Route::post('/transactions', [TransactionController::class, 'store']);
        Route::get('/transactions/{transaction}', [TransactionController::class, 'show']);
        Route::put('/transactions/{transaction}', [TransactionController::class, 'update']);
        Route::delete('/transactions/{transaction}', [TransactionController::class, 'destroy']);

        // market data
        Route::post('/update-market-data', [MarketDataController::class, 'updateMarketData']);

        // portfolio
        Route::get('/portfolio', [PortfolioController::class, 'index']);
    });
});
