<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Auth\Events\Verified;

class AuthController extends Controller
{
    // ══════════════════════════════════════════════════════════
    // REGISTRO DE USUARIO
    // ══════════════════════════════════════════════════════════

    public function register(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'department_number' => 'nullable|string|max:50',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'resident',
            'department_number' => $request->department_number,
            'department_id' => $request->department_id ?? 1,
        ]);

        event(new Registered($user));

        return response()->json([
            'message' => 'Usuario registrado exitosamente. Por favor verifica tu correo electrónico.',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
            ]
        ], 201);
    }

    // ══════════════════════════════════════════════════════════
    // LOGIN CON IDENTIFICACIÓN DE DISPOSITIVO
    // ══════════════════════════════════════════════════════════

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
            'device_name' => 'nullable|string|max:255',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas'
            ], 401);
        }

        // Comentado temporalmente para testing
        // if (!$user->hasVerifiedEmail()) {
        //     return response()->json([
        //         'message' => 'Por favor verifica tu correo electrónico',
        //         'email_verified' => false
        //     ], 403);
        // }

        // Identificar el dispositivo
        $deviceName = $request->device_name ?? $this->getDeviceName($request);

        // Crear token para este dispositivo específico
        $token = $user->createToken($deviceName)->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'department_number' => $user->department_number,
            ],
            'token' => $token,
            'device_name' => $deviceName,
            'token_type' => 'Bearer'
        ], 200);
    }

    // ══════════════════════════════════════════════════════════
    // LOGOUT (CERRAR SESIÓN EN DISPOSITIVO ACTUAL)
    // ══════════════════════════════════════════════════════════

    public function logout(Request $request)
    {
        // Eliminar solo el token del dispositivo actual
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Sesión cerrada en este dispositivo'
        ], 200);
    }

    // ══════════════════════════════════════════════════════════
    // LOGOUT DE TODOS LOS DISPOSITIVOS
    // ══════════════════════════════════════════════════════════

    public function logoutAllDevices(Request $request)
    {
        // Eliminar TODOS los tokens del usuario
        $request->user()->tokens()->delete();

        return response()->json([
            'message' => 'Sesión cerrada en todos los dispositivos'
        ], 200);
    }

    // ══════════════════════════════════════════════════════════
    // LISTAR DISPOSITIVOS ACTIVOS
    // ══════════════════════════════════════════════════════════

    public function activeDevices(Request $request)
    {
        $devices = $request->user()->tokens()->get()->map(function ($token) {
            return [
                'id' => $token->id,
                'device_name' => $token->name,
                'last_used_at' => $token->last_used_at,
                'created_at' => $token->created_at,
                'is_current' => $token->id === request()->user()->currentAccessToken()->id,
            ];
        });

        return response()->json([
            'devices' => $devices,
            'total' => $devices->count()
        ], 200);
    }

    // ══════════════════════════════════════════════════════════
    // CERRAR SESIÓN EN UN DISPOSITIVO ESPECÍFICO
    // ══════════════════════════════════════════════════════════

    public function logoutDevice(Request $request, $tokenId)
    {
        $deleted = $request->user()->tokens()->where('id', $tokenId)->delete();

        if (!$deleted) {
            return response()->json([
                'message' => 'Dispositivo no encontrado'
            ], 404);
        }

        return response()->json([
            'message' => 'Sesión cerrada en el dispositivo seleccionado'
        ], 200);
    }

    // ══════════════════════════════════════════════════════════
    // CAMBIAR CONTRASEÑA (CIERRA TODAS LAS SESIONES)
    // ══════════════════════════════════════════════════════════

    public function changePassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'current_password' => 'required|string',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = $request->user();

        // Verificar que la contraseña actual sea correcta
        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'message' => 'La contraseña actual es incorrecta'
            ], 401);
        }

        // Actualizar contraseña
        $user->password = Hash::make($request->new_password);
        $user->save();

        // IMPORTANTE: Eliminar TODOS los tokens (cierra todas las sesiones)
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Contraseña actualizada exitosamente. Se ha cerrado sesión en todos los dispositivos.'
        ], 200);
    }

    // ══════════════════════════════════════════════════════════
    // OBTENER USUARIO AUTENTICADO
    // ══════════════════════════════════════════════════════════

    public function me(Request $request)
    {
        $currentToken = $request->user()->currentAccessToken();

        return response()->json([
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'role' => $request->user()->role,
                'department_number' => $request->user()->department_number,
                'email_verified_at' => $request->user()->email_verified_at,
            ],
            'device' => [
                'name' => $currentToken->name,
                'last_used' => $currentToken->last_used_at,
            ]
        ], 200);
    }

    // ══════════════════════════════════════════════════════════
    // VERIFICAR EMAIL
    // ══════════════════════════════════════════════════════════

    public function verify(Request $request)
    {
        $user = User::find($request->route('id'));

        if (!$user) {
            return response()->json(['message' => 'Usuario no encontrado'], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'Email ya verificado'], 200);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json(['message' => 'Email verificado exitosamente'], 200);
    }

    // ══════════════════════════════════════════════════════════
    // REENVIAR EMAIL DE VERIFICACIÓN
    // ══════════════════════════════════════════════════════════

    public function resendVerification(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Email inválido',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        if ($user->hasVerifiedEmail()) {
            return response()->json(['message' => 'El email ya está verificado'], 200);
        }

        $user->sendEmailVerificationNotification();

        return response()->json(['message' => 'Email de verificación reenviado'], 200);
    }

    // ══════════════════════════════════════════════════════════
    // HELPER: IDENTIFICAR DISPOSITIVO
    // ══════════════════════════════════════════════════════════

    private function getDeviceName(Request $request)
    {
        $userAgent = $request->userAgent();
        
        // Detectar tipo de dispositivo
        if (preg_match('/mobile/i', $userAgent)) {
            if (preg_match('/iPhone/i', $userAgent)) {
                return 'iPhone';
            } elseif (preg_match('/iPad/i', $userAgent)) {
                return 'iPad';
            } elseif (preg_match('/Android/i', $userAgent)) {
                return 'Android';
            }
            return 'Móvil';
        }
        
        // Detectar navegador de escritorio
        if (preg_match('/Chrome/i', $userAgent)) {
            return 'Chrome Desktop';
        } elseif (preg_match('/Firefox/i', $userAgent)) {
            return 'Firefox Desktop';
        } elseif (preg_match('/Safari/i', $userAgent)) {
            return 'Safari Desktop';
        } elseif (preg_match('/Edge/i', $userAgent)) {
            return 'Edge Desktop';
        }
        
        return 'Navegador Web';
    }
}