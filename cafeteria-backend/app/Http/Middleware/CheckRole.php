<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class CheckRole
{
    /**
     * Handle an incoming request.
     * 
     * Uso en rutas: ->middleware('check.role:Administrador')
     */
    public function handle(Request $request, Closure $next, ...$roles)
    {
        // Verificar si el usuario está autenticado
        if (!$request->user()) {
            return response()->json([
                'success' => false,
                'message' => 'Unauthenticated'
            ], 401);
        }

        // Si no se especificaron roles, permitir
        if (empty($roles)) {
            return $next($request);
        }

        // Obtener el rol del usuario
        $userRole = $request->user()->rol->nombre ?? null;

        // Verificar si el rol del usuario está en la lista permitida
        if (!in_array($userRole, $roles)) {
            return response()->json([
                'success' => false,
                'message' => 'Forbidden'
            ], 403);
        }

        return $next($request);
    }
}
