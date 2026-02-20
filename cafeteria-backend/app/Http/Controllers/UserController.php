<?php

namespace App\Http\Controllers;

use App\Models\Usuario;
use App\Models\Auditoria;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\DB;

class UserController extends Controller
{
    /**
     * GET /api/usuarios
     * Listar todos los usuarios
     */
    public function index()
    {
        try {
            $usuarios = Usuario::with('rol')
                ->orderBy('fecha_creacion', 'desc')
                ->get();
            
            return response()->json([
                'success' => true,
                'usuarios' => $usuarios
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener usuarios',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * POST /api/usuarios
     * Crear nuevo usuario
     */
    public function store(Request $request)
    {
        try {
            $usuarioModel = new Usuario();
            $usuariosTable = $usuarioModel->getTable();

            $validated = $request->validate([
                'nombre_completo' => 'required|string|min:3|max:100',
                'nombre_usuario' => ['required','string','min:4','max:50', Rule::unique($usuariosTable, 'nombre_usuario')],
                'id_rol' => 'required|exists:rol,id_rol',
                // Usar keys ASCII en la API: 'contraseña_administrador' y 'pin_usuario'
                'contraseña_administrador' => 'required_if:id_rol,1|nullable|string|min:8',
                'pin_usuario' => 'required_unless:id_rol,1|nullable|digits:4',
                'activo' => 'boolean',
            ], [
                'nombre_completo.required' => 'El nombre completo es obligatorio',
                'nombre_usuario.required' => 'El nombre de usuario es obligatorio',
                'nombre_usuario.unique' => 'Este nombre de usuario ya está en uso',
                'activo.boolean' => 'El estado debe ser verdadero o falso',
                'id_rol.required' => 'Debe seleccionar un rol',
                'id_rol.exists' => 'El rol seleccionado no existe',
                'contraseña_administrador.required_if' => 'La contraseña es obligatoria para administradores',
                'pin_usuario.required_unless' => 'El pin es obligatorio para no administradores',
                'pin_usuario.digits' => 'El pin_usuario debe tener exactamente 4 dígitos',
            ]);

            // Mapear y hashear a las columnas existentes en la BD
            $admincontraseña_administrador = isset($validated['contraseña_administrador']) && $validated['contraseña_administrador'] !== null
                ? Hash::make($validated['contraseña_administrador'])
                : null;

            $pin_usuarioHash = isset($validated['pin_usuario']) && $validated['pin_usuario'] !== null
                ? Hash::make($validated['pin_usuario'])
                : null;

            $data = [
                'id_rol' => $validated['id_rol'],
                'nombre_completo' => $validated['nombre_completo'],
                'nombre_usuario' => $validated['nombre_usuario'],
                'contraseña_administrador' => $admincontraseña_administrador,
                'pin_usuario' => $pin_usuarioHash,
                'activo' => $validated['activo'] ?? true,
            ];

            DB::beginTransaction();
            $usuario = Usuario::create($data);
            $usuario->load('rol');

            // Registrar en auditoría
            Auditoria::create([
                'id_usuario' => $request->user()->id ?? 1,
                'tabla' => 'usuario',
                'usuario' => $request->userAgent(),
                'operacion' => 'CREATE',
                'ip' => $request->ip(),
                'fecha' => now(),
            ]);
            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Usuario creado exitosamente',
                'usuario' => $usuario
            ], 201);

        } catch (\Illuminate\Validation\ValidationException $e) {
            DB::rollBack();
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);

        } catch (\Exception $e) {
            DB::rollBack();
            // Devolver mensaje más claro al frontend para debugging
            return response()->json([
                'success' => false,
                'message' => 'Error al crear usuario',
                'error' => $e->getMessage()
            ], 500);
        }
    }

    /**
     * GET /api/usuarios/{id}
     * Mostrar un usuario específico
     */
    public function show($id)
    {
        try {
            $usuario = Usuario::with('rol')->findOrFail($id);
            
            return response()->json([
                'success' => true,
                'usuario' => $usuario
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Usuario no encontrado'
            ], 404);
        }
    }

    /**
     * PUT /api/usuarios/{id}
     * Actualizar usuario
     */
 public function update(Request $request, $id)
{
    try {
        $usuario = Usuario::findOrFail($id);
        $oldValues = $usuario->toArray();

        $validated = $request->validate([
            'nombre_completo' => 'sometimes|string|min:3|max:100',
            'nombre_usuario' => [
                'sometimes',
                'string',
                'min:4',
                'max:50',
                Rule::unique('usuario', 'nombre_usuario')->ignore($id, 'id_usuario')
            ],
            'id_rol' => 'sometimes|exists:rol,id_rol',
            'contraseña_administrador' => 'nullable|string|min:8',
                'pin_usuario' => 'nullable|digits:4',
            'activo' => 'boolean',
        ]);

        $dataToUpdate = [];

        // Campos simples
        if (isset($validated['nombre_completo'])) {
            $dataToUpdate['nombre_completo'] = $validated['nombre_completo'];
        }
        if (isset($validated['nombre_usuario'])) {
            $dataToUpdate['nombre_usuario'] = $validated['nombre_usuario'];
        }
        if (isset($validated['id_rol'])) {
            $dataToUpdate['id_rol'] = $validated['id_rol'];
        }
        if (isset($validated['activo'])) {
            $dataToUpdate['activo'] = $validated['activo'];
        }

        // contraseña_administrador: hashear antes de guardar
        if (isset($validated['contraseña_administrador']) && !empty($validated['contraseña_administrador'])) {
            $dataToUpdate['contraseña_administrador'] = Hash::make($validated['contraseña_administrador']);
        }

        // ✅ pin_usuario: hashear antes de guardar
        if (isset($validated['pin_usuario']) && !empty($validated['pin_usuario'])) {
                $dataToUpdate['pin_usuario'] = Hash::make($validated['pin_usuario']);  // ← HASHEAR
            }
    
        $usuario->update($dataToUpdate);
        $usuario->load('rol');

        // Auditoría
        Auditoria::create([
            'id_usuario' => $request->user()->id_usuario ?? 1,
            'tabla' => 'usuario',
            'operacion' => 'UPDATE',
            'ip' => $request->ip(),
            'usuario_agente' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuario actualizado exitosamente',
            'usuario' => $usuario
        ]);

    } catch (\Illuminate\Validation\ValidationException $e) {
        return response()->json([
            'success' => false,
            'message' => 'Errores de validación',
            'errors' => $e->errors()
        ], 422);
        
    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al actualizar usuario',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * DELETE /api/usuarios/{id}
     * Desactivar usuario (soft delete)
     */
  public function destroy(Request $request, $id)
{
    try {
        $usuario = Usuario::findOrFail($id);

        $currentUser = $request->user();

        // 🔒 Bloquear desactivación de la propia cuenta
        if ($currentUser && (int)$currentUser->id_usuario === (int)$usuario->id_usuario) {
            return response()->json([
                'success' => false,
                'message' => 'No puedes desactivar tu propia cuenta'
            ], 403);
        }

        $usuario->update(['activo' => false]);

        Auditoria::create([
            'id_usuario' => $currentUser->id_usuario ?? 1,
            'tabla' => 'usuario',
            'id_registro' => $usuario->id_usuario,
            'operacion' => 'DELETE',
            'ip' => $request->ip(),
            'usuario' => $request->userAgent(),
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Usuario desactivado exitosamente'
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'success' => false,
            'message' => 'Error al desactivar usuario',
            'error' => $e->getMessage()
        ], 500);
    }
}

    /**
     * GET /api/usuarios/auditoria/{id}
     * Obtener log de auditoría de un usuario
     */
    public function auditoria($id)
    {
        try {
            $logs = Auditoria::with('usuario:id_usuario,nombre_completo,nombre_usuario')
                ->where('tabla', 'usuario')
                ->where('id_auditoria', $id)
                ->orderBy('fecha', 'desc')
                ->get();

            return response()->json([
                'success' => true,
                'logs' => $logs
            ]);

        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener auditoría'
            ], 500);
        }
    }
}
