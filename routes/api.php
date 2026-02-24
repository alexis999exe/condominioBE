<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ChatController;

/*
|--------------------------------------------------------------------------
| API Routes - Protegidas con Sanctum + Roles
|--------------------------------------------------------------------------
*/

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
    
    // ─── Auth ───
    Route::post('/auth/logout', [AuthController::class, 'logout']);
    Route::get('/auth/me', [AuthController::class, 'me']);

    // ─── Chat (todos los usuarios autenticados) ───
    Route::get('/chat/messages', [ChatController::class, 'getMessages']);
    Route::post('/chat/messages', [ChatController::class, 'sendMessage']);

    // ─── Notificaciones (todos los usuarios autenticados) ───
    Route::get('/notifications', [NotificationController::class, 'index']);
    Route::post('/notifications/{id}/read', [NotificationController::class, 'markAsRead']);
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead']);

    // ══════════════════════════════════════════════════════════
    // RUTAS SOLO PARA ADMIN
    // ══════════════════════════════════════════════════════════

    Route::middleware('role:admin')->group(function () {
        
        // ─── Gestión de usuarios ───
        Route::get('/admin/users', function () {
            return response()->json([
                'users' => \App\Models\User::all()
            ]);
        });

        Route::post('/admin/users/{id}/promote', function ($id) {
            $user = \App\Models\User::findOrFail($id);
            $user->role = 'admin';
            $user->save();

            return response()->json([
                'message' => 'Usuario promovido a admin',
                'user' => $user
            ]);
        });

        Route::post('/admin/users/{id}/demote', function ($id) {
            $user = \App\Models\User::findOrFail($id);
            $user->role = 'resident';
            $user->save();

            return response()->json([
                'message' => 'Usuario degradado a resident',
                'user' => $user
            ]);
        });

        // ─── Enviar notificaciones (solo admin) ───
        Route::post('/admin/notifications/send', [NotificationController::class, 'sendNotification']);

        // ─── Broadcast de mensajes (solo admin) ───
        Route::post('/admin/chat/broadcast', [ChatController::class, 'broadcastMessage']);
    });
});