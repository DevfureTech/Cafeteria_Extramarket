<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Sesion;
use Carbon\Carbon;

class CheckInactivity
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();

        if (!$token) {
            return response()->json([
                'message' => 'Token no proporcionado'
            ], 401);
        }

        $sesion = Sesion::where('token', hash('sha256', $token))
            ->where('activo', true)
            ->first();

        if (!$sesion) {
            return response()->json([
                'message' => 'Sesión inválida'
            ], 401);
        }

        if (Carbon::now()->diffInMinutes($sesion->ultimo_acceso) >= 30) {
            $sesion->update(['activo' => false]);

            return response()->json([
                'message' => 'Sesión cerrada por inactividad'
            ], 401);
        }

        $sesion->update([
            'ultimo_acceso' => now()
        ]);

        return $next($request);
    }
}

