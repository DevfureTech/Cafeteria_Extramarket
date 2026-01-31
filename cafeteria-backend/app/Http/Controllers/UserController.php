<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use Illuminate\Http\Request;

class UserController extends Controller
{
    
    public function index()
    {
        $usuarios = Usuario::with('rol')
            ->activos()  
            ->get();
        
        return response()->json([
            'success' => true,
            'usuarios' => $usuarios
        ]);
    }

    public function create()
    {
        //
    }

    public function store(Request $request)
    {
         $validated = $request->validate([
            'nombreUsuario' => 'required|unique:usuario',
            'id_rol' => 'required|exists:rol,id',
            'password' => 'required_if:rol_id,1|min:8',
            'pin' => 'required_unless:rol_id,1|digits:4',
        ]);

        $usuario = Usuario::create($validated);
        $usuario->load('rol');
        
        return response()->json([
            'success' => true,
            'message' => 'Usuario creado exitosamente',
            'usuario' => $usuario
        ], 201);
    }


    public function show(string $id)
    {
        $usuario = Usuario::with(['rol', 'sesiones'])
            ->findOrFail($id);
        
        return response()->json([
            'success' => true,
            'usuario' => $usuario
        ]);
    }

    public function edit(string $id)
    {
        //
    }

    public function update(Request $request, string $id)
    {
        $usuario = Usuario::findOrFail($id);
        
        $validated = $request->validate([
            'nombre_usuario' => 'sometimes|unique:usuario,username,' . $id,
            'id_rol' => 'sometimes|exists:rol,id',
            'password' => 'nullable|min:8',
            'pin' => 'nullable|digits:4',
            'activo' => 'boolean',
        ]);
        
        $usuario->update($validated);
        $usuario->load('rol');
        
        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado exitosamente',
            'usuario' => $usuario
        ]);
    }


    public function destroy(string $id)
    {
         $usuario = Usuario::findOrFail($id);
        
       
        $usuario->update(['activo' => false]);
        
        return response()->json([
            'success' => true,
            'message' => 'Usuario desactivado exitosamente'
        ]);
    }

     public function porRol($rol)
    {
        $usuarios = Usuario::with('rol')
            ->porRol($rol)
            ->activos()
            ->get();
        
        return response()->json([
            'success' => true,
            'usuarios' => $usuarios
        ]);
    }
}
