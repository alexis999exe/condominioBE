<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Ensure the authenticated user has one of the required roles.
     *
     * Usage in routes:
     *   ->middleware('role:admin')
     *   ->middleware('role:admin,resident')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        if (! $request->user()) {
            return response()->json([
                'message' => 'No autenticado.',
                'code'    => 'UNAUTHENTICATED',
            ], 401);
        }

        if (! in_array($request->user()->role, $roles)) {
            return response()->json([
                'message'        => 'No tienes permisos para acceder a este recurso.',
                'code'           => 'FORBIDDEN',
                'required_roles' => $roles,
                'your_role'      => $request->user()->role,
            ], 403);
        }

        return $next($request);
    }
}