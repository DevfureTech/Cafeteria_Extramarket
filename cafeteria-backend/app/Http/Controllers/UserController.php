<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Http\Requests\UserRequest;
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

    public function store(StoreUsuarioRequest $request)
    {
         $validated = $request->validated();
        
        // Crear usuario
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
        
        $validated = $request->validated();
        
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
