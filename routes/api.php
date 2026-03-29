<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\Api\ChatController;
use App\Http\Controllers\Api\NotificationController;

// ══════════════════════════════════════════════════════════
// RUTAS PÚBLICAS (sin autenticación)
// ══════════════════════════════════════════════════════════

Route::post('/auth/register', [AuthController::class, 'register']);
Route::post('/auth/login',    [AuthController::class, 'login']);
Route::post('/auth/resend-verification', [AuthController::class, 'resendVerification']);
Route::get('/auth/verify/{id}/{hash}',   [AuthController::class, 'verify'])
    ->name('verification.verify');

// ══════════════════════════════════════════════════════════
// RUTAS PROTEGIDAS
// auth:sanctum   → valida el Bearer token (Sanctum)
// active.session → verifica que el usuario sigue activo
// ══════════════════════════════════════════════════════════

Route::middleware(['auth:sanctum', 'active.session'])->group(function () {

    // ── Auth / perfil ──────────────────────────────────────
    Route::get('/auth/me',     [AuthController::class, 'me']);
    Route::post('/auth/logout', [AuthController::class, 'logout']);

    // ── Gestión de dispositivos ────────────────────────────
    Route::get('/auth/devices',                   [AuthController::class, 'activeDevices']);
    Route::post('/auth/logout-all',               [AuthController::class, 'logoutAllDevices']);
    Route::delete('/auth/devices/{tokenId}',      [AuthController::class, 'logoutDevice']);

    // ── Cambio de contraseña ───────────────────────────────
    Route::post('/auth/change-password', [AuthController::class, 'changePassword']);

    // ══════════════════════════════════════════════════════
    // CHAT  (accesible a todos los usuarios autenticados)
    // ══════════════════════════════════════════════════════
    Route::prefix('chat')->group(function () {
        Route::get('/messages',         [ChatController::class, 'index']);
        Route::post('/messages',        [ChatController::class, 'store']);
        Route::delete('/messages/{message}', [ChatController::class, 'destroy']);
    });

    // ══════════════════════════════════════════════════════
    // NOTIFICACIONES (todos los usuarios autenticados)
    // ══════════════════════════════════════════════════════
    Route::prefix('notifications')->group(function () {
        Route::get('/',              [NotificationController::class, 'index']);
        Route::post('/',             [NotificationController::class, 'store']);
        Route::post('/{notification}/read', [NotificationController::class, 'markAsRead']);
        Route::post('/read-all',     [NotificationController::class, 'markAllAsRead']);
    });

    // ══════════════════════════════════════════════════════
    // RUTAS SOLO PARA ADMINISTRADORES
    // role:admin  → sólo rol "admin" puede acceder
    // ══════════════════════════════════════════════════════
    Route::middleware('role:admin')->prefix('admin')->group(function () {

        // Aquí irán los endpoints exclusivos de administración,
        // por ejemplo gestión de departamentos, usuarios, reportes, etc.
        // Route::get('/users',       [UserController::class, 'index']);
        // Route::get('/departments', [DepartmentController::class, 'index']);

        // Placeholder para que el grupo no quede vacío
        Route::get('/status', function () {
            return response()->json([
                'message' => 'Panel de administración activo.',
                'admin'   => auth()->user()->name,
            ]);
        });
    });

    // ══════════════════════════════════════════════════════
    // RUTAS SOLO PARA RESIDENTES
    // role:resident  → sólo rol "resident" puede acceder
    // ══════════════════════════════════════════════════════
    Route::middleware('role:resident')->prefix('resident')->group(function () {

        // Aquí irán los endpoints exclusivos de residentes
        // Route::get('/my-department', [DepartmentController::class, 'myDepartment']);

        Route::get('/status', function () {
            return response()->json([
                'message'  => 'Área de residente activa.',
                'resident' => auth()->user()->name,
            ]);
        });
    });

    // ══════════════════════════════════════════════════════
    // RUTAS PARA ADMIN O RESIDENT (ambos roles permitidos)
    // ══════════════════════════════════════════════════════
    Route::middleware('role:admin,resident')->group(function () {
        // Ejemplo: ambos roles pueden ver el directorio del condominio
        // Route::get('/directory', [DirectoryController::class, 'index']);
    });
});