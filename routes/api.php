<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\NotificationController;
use Illuminate\Support\Facades\Route;

// ─── Auth ────────────────────────────────────────
Route::prefix('auth')->group(function () {
    Route::post('/login',  [AuthController::class, 'login']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me',      [AuthController::class, 'me']);
});

// ─── Chat (existente) ────────────────────────────
Route::prefix('chat')->group(function () {
    Route::get('/messages',            [ChatController::class, 'index']);
    Route::post('/messages',           [ChatController::class, 'store']);
    Route::delete('/messages/{message}', [ChatController::class, 'destroy']);
});

// ─── Notificaciones ──────────────────────────────
Route::prefix('notifications')->group(function () {
    Route::get('/',                        [NotificationController::class, 'index']);
    Route::post('/',                       [NotificationController::class, 'store']);
    Route::post('/{notification}/read',    [NotificationController::class, 'markAsRead']);
    Route::post('/read-all',               [NotificationController::class, 'markAllAsRead']);
});
