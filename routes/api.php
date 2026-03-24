<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;

// ══════════════════════════════════════════════════════════
// RUTAS PÚBLICAS (sin autenticación)
// ══════════════════════════════════════════════════════════

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login', [AuthController::class, 'login']);
Route::post('/auth/resend-verification', [AuthController::class, 'resendVerification']);
Route::get('/auth/verify/{id}/{hash}', [AuthController::class, 'verify'])
    ->name('verification.verify');

// ══════════════════════════════════════════════════════════
// RUTAS PROTEGIDAS (requieren autenticación con Sanctum)
// ══════════════════════════════════════════════════════════

Route::middleware('auth:sanctum')->group(function () {
    
    // ─── Auth y Usuario ───
    Route::get('/auth/me', [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    
    // ─── Gestión de Dispositivos ───
    Route::get('/auth/devices', [AuthController::class, 'activeDevices']);
    Route::post('/auth/logout-all', [AuthController::class, 'logoutAllDevices']);
    Route::delete('/auth/devices/{tokenId}', [AuthController::class, 'logoutDevice']);
    
    // ─── Cambio de Contraseña ───
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);
});