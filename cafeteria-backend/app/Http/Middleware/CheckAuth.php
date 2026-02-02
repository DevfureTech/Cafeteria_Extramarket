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
        $token = $request -> bearerToken(); 
        if(!$token){
            return response() -> json([
                'success:' =>false, 
                'message:' => "Token no proporcionado",
            ], 401); 
        }

        $tokenHash = hash('sha296', $token); 

        $sesion = Sesion::with('usuario')
            ->where('token', $token)
            ->where('activo', true) 
            ->first(); 
          
        if(!$sesion){
            return response() -> json([
                'success:' =>false, 
                'message:' => "Sesion cerrada o invalida",
            ], 401); 
        }

        if ($sesion->fecha_expiracion < now()) {
            $sesion->update(['activo' => false]);
            return response()->json([
                'success' => false,
                'message' => 'Sesión expirada'
            ], 401);
        }
        if (Carbon::now()->greaterThan($sesion->fecha_expiracion)) {
            $sesion->update(['activo' => false]);

            return response()->json([
                'message' => 'Token expirado'
            ], 401);
        }

        $sesion->update([
            'fecha_expiracion' => now()->addMinutes(30)
        ]);

        // Inyectar usuario autenticado
        auth()->loginUsingId($sesion->id_usuario);

        return $next($request);


         $request->setUserResolver(function () use ($sesion) {
            return $sesion->usuario;
        });
        return $next($request);


    }
}
