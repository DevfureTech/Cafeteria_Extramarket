<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Sesion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function login(Request $request)
{
    $request->validate([
        'nombre_usuario' => 'required',
        'contraseña_administrador' => 'nullable',
        'pin' => 'nullable',
    ]);

<<<<<<< HEAD
    $usuario = Usuario::with('rol.permisos')
=======
    $usuario = Usuario::with('rol')
>>>>>>> respaldo-local
        ->where('nombre_usuario', $request->nombre_usuario)
        ->where('activo', 1)
        ->first();

    if (!$usuario) {
        return response()->json([
            'success' => false,
            'message' => ''
        ], 401);
    }

    if ($usuario->esAdmin()) {
        if (!Hash::check($request->contraseña_administrador, $usuario->contraseña_administrador)) {
            return response()->json(['message' => 'Contraseña incorrecta'], 401);
        }
    } else {
       if (!Hash::check($request->pin, $usuario->pin_usuario)) {
    return response()->json(['message' => 'PIN incorrecto'], 401);
}


    }

    $tokenResult = $usuario->createToken('api-token');
    $plainToken = $tokenResult->plainTextToken;
    $hashedToken = hash('sha256', explode('|', $plainToken)[1]);

    Sesion::create([
        'id_usuario' => $usuario->id_usuario,
        'token' => $hashedToken,
        'ip' => $request->ip(),
        'user_agent' => $request->userAgent(),
        'fecha_inicio' => now(),
        'fecha_expiracion' => now()->addHours(2),
        'activo' => 1
    ]);

    return response()->json([
        'success' => true,
        'token' => $plainToken,
        'usuario' => $usuario,
        'permisos' => $usuario->rol->permisosAgrupados()
    ]);
}
public function logout(Request $request)
{
    try {
        // Obtener token del header
        $token = $request->bearerToken();
        
        if ($token) {
            // Hashear el token para buscarlo en la BD
            $hashedToken = hash('sha256', $token);
            
            // Buscar sesión activa y desactivarla
            $sesion = Sesion::where('token', $hashedToken)
                ->where('activo', true)
                ->first();
            
            if ($sesion) {
                $sesion->update(['activo' => false]);
            }
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout exitoso'
        ]);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al cerrar sesión',
            'error' => $e->getMessage()
        ], 500);
    }
}


    public function me(Request $request)
    {
        $usuario = $request->user();
        $usuario->load('rol');

        return response()->json([
            'success' => true,
            'usuario' => $usuario,
            'permisos' => $usuario->rol->permisosAgrupados(),
        ]);
    }

    public function verificarPermiso(Request $request)
    {
        $request->validate([
            'modulo' => 'required',
            'accion' => 'required|in:crear,leer,actualizar,eliminar',
        ]);

        $usuario = $request->user();

        $tienePermiso = $usuario->tienePermiso(
            $request->modulo,
            $request->accion
        );

        return response()->json([
            'success' => true,
            'tiene_permiso' => $tienePermiso
        ]);
    }
}
