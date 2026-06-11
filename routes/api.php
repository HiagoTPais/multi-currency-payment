<?php

use App\Http\Controllers\Api\DashboardController;
use App\Http\Controllers\Api\PaymentRequestController;
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::prefix('auth')->group(function (): void {
    Route::post('register', [AuthController::class, 'register']);
    Route::post('login', [AuthController::class, 'login']);

    Route::middleware('auth:api')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout']);
        Route::get('me', [AuthController::class, 'me']);
    });
});

Route::middleware('auth:api')->group(function (): void {
    Route::get('dashboard', DashboardController::class);

    Route::get('payment-requests', [PaymentRequestController::class, 'index']);
    Route::post('payment-requests', [PaymentRequestController::class, 'store'])->middleware('role:employee');
    Route::get('payment-requests/{paymentRequest}', [PaymentRequestController::class, 'show']);
    Route::patch('payment-requests/{paymentRequest}/approve', [PaymentRequestController::class, 'approve'])->middleware('role:finance');
    Route::patch('payment-requests/{paymentRequest}/reject', [PaymentRequestController::class, 'reject'])->middleware('role:finance');
});
