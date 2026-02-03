<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * Login del usuario.
     * POST /api/auth/login
     * Body: { email, password }
     */
    public function login(Request $request)
    {
        $request->validate([
            'email'    => 'required|string|email',
            'password' => 'required|string',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user || !Hash::check($request->password, $user->password)) {
            return response()->json([
                'message' => 'Credenciales incorrectas.',
            ], 401);
        }

        // Generar token (usando Sanctum)
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'user'   => [
                'id'            => $user->id,
                'name'          => $user->name,
                'email'         => $user->email,
                'role'          => $user->role ?? 'resident',
                'department_id' => $user->department_id,
            ],
            'token' => $token,
        ]);
    }

    /**
     * Logout del usuario.
     * POST /api/auth/logout
     */
    public function logout(Request $request)
    {
        // Si usa Sanctum tokens
        if ($request->user()) {
            $request->user()->currentAccessToken()->delete();
        }

        return response()->json([
            'status'  => 'success',
            'message' => 'Sesión cerrada correctamente.',
        ]);
    }

    /**
     * Obtener datos del usuario actual.
     * GET /api/auth/me
     */
    public function me(Request $request)
    {
        // Temporal: acepta user_id por query param mientras no haya auth real
        $userId = $request->get('user_id');
        
        if ($userId) {
            $user = User::find($userId);
            if ($user) {
                return response()->json([
                    'status' => 'success',
                    'user'   => [
                        'id'            => $user->id,
                        'name'          => $user->name,
                        'email'         => $user->email,
                        'role'          => $user->role ?? 'resident',
                        'department_id' => $user->department_id,
                    ],
                ]);
            }
        }

        return response()->json([
            'message' => 'No autenticado.',
        ], 401);
    }
}
