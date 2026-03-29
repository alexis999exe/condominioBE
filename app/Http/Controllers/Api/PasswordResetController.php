<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Mail\PasswordResetCodeMail;
use App\Models\PasswordResetCode;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;

class PasswordResetController extends Controller
{
    // ══════════════════════════════════════════════════════════
    // PASO 1: Solicitar código
    // POST /api/password/send-code
    // Body: { email }
    // ══════════════════════════════════════════════════════════

    public function sendCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Correo inválido.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Siempre respondemos igual para no revelar si el email existe
        $user = User::where('email', $request->email)->first();

        if ($user) {
            // Invalidar cualquier código anterior para este email
            PasswordResetCode::where('email', $request->email)
                ->where('used', false)
                ->update(['used' => true]);

            // Generar código de 6 dígitos
            $code = str_pad(random_int(0, 999999), 6, '0', STR_PAD_LEFT);

            // Guardar en BD con expiración de 10 minutos
            PasswordResetCode::create([
                'email'      => $request->email,
                'code'       => $code,
                'used'       => false,
                'expires_at' => now()->addMinutes(10),
            ]);

            // Enviar correo
            Mail::to($request->email)->send(
                new PasswordResetCodeMail($code, $user->name)
            );
        }

        // Respuesta genérica (no revela si el email existe)
        return response()->json([
            'message' => 'Si el correo está registrado, recibirás un código en tu bandeja de entrada.',
        ], 200);
    }

    // ══════════════════════════════════════════════════════════
    // PASO 2: Verificar código
    // POST /api/password/verify-code
    // Body: { email, code }
    // ══════════════════════════════════════════════════════════

    public function verifyCode(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'code'  => 'required|string|size:6',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        $resetCode = PasswordResetCode::where('email', $request->email)
            ->where('code', $request->code)
            ->latest()
            ->first();

        // Código no encontrado
        if (! $resetCode) {
            return response()->json([
                'message' => 'Código incorrecto.',
                'code'    => 'INVALID_CODE',
            ], 400);
        }

        // Código ya usado
        if ($resetCode->isUsed()) {
            return response()->json([
                'message' => 'Este código ya fue utilizado.',
                'code'    => 'CODE_ALREADY_USED',
            ], 400);
        }

        // Código expirado
        if ($resetCode->isExpired()) {
            return response()->json([
                'message' => 'El código ha expirado. Solicita uno nuevo.',
                'code'    => 'CODE_EXPIRED',
            ], 400);
        }

        // Código válido — devolver un token temporal para el paso 3
        // Usamos el ID firmado del registro como token de un solo uso
        $resetToken = encrypt($resetCode->id . '|' . $request->email);

        return response()->json([
            'message'      => 'Código verificado correctamente.',
            'reset_token'  => $resetToken,
        ], 200);
    }

    // ══════════════════════════════════════════════════════════
    // PASO 3: Restablecer contraseña
    // POST /api/password/reset
    // Body: { reset_token, password, password_confirmation }
    // ══════════════════════════════════════════════════════════

    public function resetPassword(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'reset_token'           => 'required|string',
            'password'              => 'required|string|min:8|confirmed',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Datos inválidos.',
                'errors'  => $validator->errors(),
            ], 422);
        }

        // Desencriptar el token temporal
        try {
            $decrypted = decrypt($request->reset_token);
            [$resetCodeId, $email] = explode('|', $decrypted, 2);
        } catch (\Exception $e) {
            return response()->json([
                'message' => 'Token inválido o manipulado.',
                'code'    => 'INVALID_TOKEN',
            ], 400);
        }

        // Verificar que el código aún exista y sea válido
        $resetCode = PasswordResetCode::find($resetCodeId);

        if (! $resetCode || $resetCode->email !== $email || ! $resetCode->isValid()) {
            return response()->json([
                'message' => 'Token inválido o expirado. Inicia el proceso nuevamente.',
                'code'    => 'TOKEN_INVALID_OR_EXPIRED',
            ], 400);
        }

        // Buscar al usuario
        $user = User::where('email', $email)->first();

        if (! $user) {
            return response()->json([
                'message' => 'Usuario no encontrado.',
                'code'    => 'USER_NOT_FOUND',
            ], 404);
        }

        // Actualizar contraseña
        $user->password = Hash::make($request->password);
        $user->save();

        // Marcar el código como usado
        $resetCode->markAsUsed();

        // Revocar todos los tokens activos (cerrar todas las sesiones)
        $user->tokens()->delete();

        return response()->json([
            'message' => 'Contraseña restablecida exitosamente. Por favor inicia sesión con tu nueva contraseña.',
        ], 200);
    }
}