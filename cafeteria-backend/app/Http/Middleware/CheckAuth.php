<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use App\Models\Sesion; 
use Carbon\Carbon; 

class CheckAuth
{
    public function handle(Request $request, Closure $next): Response
    {
        $token = $request->bearerToken();
        if (!$token) {
            return response()->json([
                'success' => false,
                'message' => 'Token no proporcionado'
            ], 401);
        }

        // Parse token to get the actual token part (after |)
        $tokenParts = explode('|', $token);
        if (count($tokenParts) !== 2) {
            return response()->json([
                'success' => false,
                'message' => 'Token inválido'
            ], 401);
        }
        $tokenHash = hash('sha256', $tokenParts[1]);

        $sesion = Sesion::with('usuario')
            ->where('token', $tokenHash)
            ->where('activo', true)
            ->first();

        if (!$sesion) {
            return response()->json([
                'success' => false,
                'message' => 'Sesión cerrada o inválida'
            ], 401);
        }

        if ($sesion->fecha_expiracion < now()) {
            $sesion->update(['activo' => false]);
            return response()->json([
                'success' => false,
                'message' => 'Sesión expirada'
            ], 401);
        }

        $sesion->update([
            'fecha_expiracion' => now()->addMinutes(30)
        ]);

        // Set user resolver for $request->user()
        $request->setUserResolver(function () use ($sesion) {
            return $sesion->usuario;
        });

        return $next($request);
    }
}
