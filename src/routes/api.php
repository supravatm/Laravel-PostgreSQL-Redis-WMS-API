<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ProductController;
use App\Http\Controllers\Api\LocationController;
use App\Http\Controllers\Api\WarehouseController;
use App\Http\Controllers\Api\InventoryController;
use App\Http\Controllers\Api\InventoryOperationController;
use App\Http\Controllers\Api\StockMovementController;


Route::prefix('auth')->group(function () {

    Route::post('/register', [AuthController::class, 'register']);

    Route::post('/login', [AuthController::class, 'login']);

    Route::middleware('auth:sanctum')->group(function () {
        Route::get('/me', [AuthController::class, 'me']);
        Route::post('/logout', [AuthController::class, 'logout']);
    });
});

Route::middleware('auth:sanctum')->group(function () {

    Route::apiResource(
        'products',
        ProductController::class
    );
    Route::apiResource(
        'warehouses',
        WarehouseController::class
    );
    Route::get(
        'warehouses/{warehouse}/locations',
        [LocationController::class, 'index']
    );

    Route::post(
        'warehouses/{warehouse}/locations',
        [LocationController::class, 'store']
    );

    Route::apiResource('locations', LocationController::class)
        ->except(['index', 'store']);

    Route::get(
        '/inventory',
        [InventoryController::class, 'index']
    );

    Route::post(
        '/inventory/receive',
        [InventoryOperationController::class, 'receive']
    );

    Route::post(
        '/inventory/dispatch',
        [InventoryOperationController::class, 'dispatch']
    );

    Route::post(
        '/inventory/receive',
        [InventoryOperationController::class, 'receive']
    );

    Route::post(
        '/inventory/transfer',
        [InventoryOperationController::class, 'transfer']
    );

    Route::post(
        '/inventory/dispatch',
        [InventoryOperationController::class, 'dispatch']
    );
    Route::get(
        '/stock-movements',
        [StockMovementController::class, 'index']
    );
});
