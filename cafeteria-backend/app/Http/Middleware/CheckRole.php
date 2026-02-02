<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;


class CheckRole
{

    public function handle(Request $request, Closure $next): Response
    {
         $usuario = auth()->user();

        if (!$usuario) {
            return response()->json([
                'message' => 'Usuario no autenticado'
            ], 401);
        }
        if (!$usuario->tienePermiso($modulo, $accion)) {
            return response()->json([
                'message' => 'No tiene permisos para esta acción'
            ], 403);
        }

        return $next($request);
    }
}
