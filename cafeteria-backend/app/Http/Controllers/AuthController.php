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
            'contraseña_usuario' => 'nullable',
            'pin' => 'nullable',
        ]);
        $usuario = Usuario::with('rol')
            ->where('nombre_usuario', $request->nombre_usuario)
            ->where('activo', true)
            ->first();

        if (!$usuario) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado o inactivo'
            ], 401);
        }
        if ($usuario->esAdmin()) {
            // Administrador usa password
            if (!Hash::check($request->contraseña_administrador, $usuario->contraseña_administrador)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Contraseña incorrecta'
                ], 401);
            }
        } else {
            if ($request->pin !== $usuario->pin_usuario) {
                return response()->json([
                    'success' => false,
                    'message' => 'PIN incorrecto'
                ], 401);
            }
        }
        $token = Str::random(60);

        $sesion = Sesion::create([
            'id' => $usuario->id,
            'token' => hash('sha256', $token),
            'ip_address' => $request->ip(),
            'user_agent' => $request->userAgent(),
            'fecha_expiracion' => now()->addMinutes(30),
            'activo' => true,
        ]);

        // Actualizar último acceso
        $usuario->update(['ultimo_acceso' => now()]);

        return response()->json([
            'success' => true,
            'message' => 'Login exitoso',
            'token' => $token,
            'usuario' => $usuario,
            'permisos' => $usuario->rol->permisosAgrupados(),
        ]);
    }

    public function logout(Request $request)
    {
        $token = $request->bearerToken();
        
        if ($token) {
            Sesion::where('token', hash('sha256', $token))
                ->update(['activo' => false]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Logout exitoso'
        ]);
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
