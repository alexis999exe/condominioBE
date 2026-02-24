<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Password;
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
            'department_id' => 'nullable|exists:departments,id',
        ]);

        if ($validator->fails()) {
            return response()->json([
                'message' => 'Error de validación',
                'errors' => $validator->errors()
            ], 422);
        }

        // Crear usuario (por defecto es resident)
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'role' => 'resident', // Todos empiezan como residentes
            'department_number' => $request->department_number,
            'department_id' => $request->department_id,
        ]);

        // Disparar evento de registro (envía email de verificación)
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
    // LOGIN
    // ══════════════════════════════════════════════════════════

    public function login(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'email' => 'required|email',
            'password' => 'required|string',
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

        // Verificar si el email está verificado
        if (!$user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Por favor verifica tu correo electrónico antes de iniciar sesión',
                'email_verified' => false
            ], 403);
        }

        // Crear token de Sanctum
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'message' => 'Login exitoso',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'role' => $user->role,
                'department_id' => $user->department_id,
                'department_number' => $user->department_number,
            ],
            'token' => $token,
            'token_type' => 'Bearer'
        ], 200);
    }

    // ══════════════════════════════════════════════════════════
    // LOGOUT
    // ══════════════════════════════════════════════════════════

    public function logout(Request $request)
    {
        // Eliminar el token actual del usuario
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logout exitoso'
        ], 200);
    }

    // ══════════════════════════════════════════════════════════
    // VERIFICAR EMAIL
    // ══════════════════════════════════════════════════════════

    public function verify(Request $request)
    {
        $user = User::find($request->route('id'));

        if (!$user) {
            return response()->json([
                'message' => 'Usuario no encontrado'
            ], 404);
        }

        if ($user->hasVerifiedEmail()) {
            return response()->json([
                'message' => 'Email ya verificado'
            ], 200);
        }

        if ($user->markEmailAsVerified()) {
            event(new Verified($user));
        }

        return response()->json([
            'message' => 'Email verificado exitosamente'
        ], 200);
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
            return response()->json([
                'message' => 'El email ya está verificado'
            ], 200);
        }

        $user->sendEmailVerificationNotification();

        return response()->json([
            'message' => 'Email de verificación reenviado'
        ], 200);
    }

    // ══════════════════════════════════════════════════════════
    // OBTENER USUARIO AUTENTICADO
    // ══════════════════════════════════════════════════════════

    public function me(Request $request)
    {
        return response()->json([
            'user' => [
                'id' => $request->user()->id,
                'name' => $request->user()->name,
                'email' => $request->user()->email,
                'role' => $request->user()->role,
                'department_id' => $request->user()->department_id,
                'department_number' => $request->user()->department_number,
                'email_verified_at' => $request->user()->email_verified_at,
            ]
        ], 200);
    }
}