<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\PasswordResetCode;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use App\Notifications\PasswordResetCodeNotification;

class PasswordResetController extends Controller
{
    // ══════════════════════════════════════════════════════════
    // PASO 1: SOLICITAR CÓDIGO (enviar email)
    // ══════════════════════════════════════════════════════════

    public function sendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email|exists:users,email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Email inválido o no registrado',
                'errors' => $validator->errors()
            ], 422);
        }

        $user = User::where('email', $request->email)->first();

        // Invalidar códigos anteriores del mismo email
        PasswordResetCode::where('email', $request->email)
            ->where('used', false)
            ->update(['used' => true]);

        // Generar nuevo código
        $code = PasswordResetCode::generateCode();

        // Guardar en base de datos
        PasswordResetCode::create([
            'email' => $request->email,
            'code' => $code,
            'expires_at' => now()->addMinutes(10), // Expira en 10 minutos
            'used' => false,
        ]);

        // Enviar email
        try {
            $user->notify(new PasswordResetCodeNotification($code));
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Error al enviar el correo. Intenta de nuevo.',
                'error' => $e->getMessage()
            ], 500);
        }

        return response()->json([
            'message' => 'Código enviado a tu correo electrónico',
            'email' => $request->email,
        ], 200);
    }

    // ══════════════════════════════════════════════════════════
    // PASO 2: VERIFICAR CÓDIGO
    // ══════════════════════════════════════════════════════════

    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos',
                'errors' => $validator->errors()
            ], 422);
        }

        // Buscar código
        $resetCode = PasswordResetCode::where('email', $request->email)
            ->where('code', $request->code)
            ->where('used', false)
            ->first();

        if (!$resetCode) {
            return response()->json([
                'message' => 'Código inválido o ya utilizado'
            ], 400);
        }

        // Verificar expiración
        if ($resetCode->isExpired()) {
            return response()->json([
                'message' => 'El código ha expirado. Solicita uno nuevo.'
            ], 400);
        }

        return response()->json([
            'message' => 'Código verificado correctamente',
            'valid' => true
        ], 200);
    }

    // ══════════════════════════════════════════════════════════
    // PASO 3: RESTABLECER CONTRASEÑA
    // ══════════════════════════════════════════════════════════

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code' => 'required|string|size:6',
            'password' => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Buscar código
        $resetCode = PasswordResetCode::where('email', $request->email)
            ->where('code', $request->code)
            ->where('used', false)
            ->first();

        if (!$resetCode) {
            return response()->json([
                'message' => 'Código inválido o ya utilizado'
            ], 400);
        }

        // Verificar expiración
        if ($resetCode->isExpired()) {
            return response()->json([
                'message' => 'El código ha expirado. Solicita uno nuevo.'
            ], 400);
        }

        // Buscar usuario
        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        // Actualizar contraseña
        $user->password = Hash::make($request->password);
        $user->save();

        // Marcar código como usado
        $resetCode->markAsUsed();

        // IMPORTANTE: Cerrar todas las sesiones (eliminar tokens)
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Contraseña restablecida exitosamente. Inicia sesión con tu nueva contraseña.'
        ], 200);
    }

    // ══════════════════════════════════════════════════════════
    // REENVIAR CÓDIGO
    // ══════════════════════════════════════════════════════════

    public function resendCode(Request $request)
    {
        // Reutilizar la misma lógica de sendCode
        return $this->sendCode($request);
    }
}