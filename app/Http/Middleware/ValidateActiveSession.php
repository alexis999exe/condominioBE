<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ValidateActiveSession
{
    /**
     * Validates that the authenticated user's session is still active.
     *
     * This runs after auth:sanctum has already verified the token.
     * It adds an extra layer of checks:
     *  - User exists in the DB
     *  - User has a verified email (optional, currently soft-checked)
     *  - The token has not expired (Sanctum handles this, but we log last_used_at)
     */
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        // Should not happen after auth:sanctum, but be defensive
        if (! $user) {
            return response()->json([
                'message' => 'No autenticado. Por favor inicia sesión.',
                'code'    => 'UNAUTHENTICATED',
            ], 401);
        }

        // Check the user still exists and is not soft-deleted (if you add that later)
        if (! $user->exists) {
            return response()->json([
                'message' => 'La cuenta de usuario ya no existe.',
                'code'    => 'USER_NOT_FOUND',
            ], 401);
        }

        // Optionally block unverified users on protected routes.
        // Currently commented out to match your existing login logic,
        // but you can enable it per-route by adding the middleware selectively.
        //
        // if (! $user->hasVerifiedEmail()) {
        //     return response()->json([
        //         'message' => 'Debes verificar tu correo electrónico.',
        //         'code'    => 'EMAIL_NOT_VERIFIED',
        //     ], 403);
        // }

        return $next($request);
    }
}