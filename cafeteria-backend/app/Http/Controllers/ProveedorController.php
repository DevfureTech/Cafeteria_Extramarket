<?php

namespace App\Http\Controllers;

use App\Models\Proveedor;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class ProveedorController extends Controller
{
    // ══════════════════════════════════════════════════════════
    // LISTAR
    // ══════════════════════════════════════════════════════════

    /**
     * GET /api/proveedores
     */
    public function index(): JsonResponse
    {
        $proveedores = Proveedor::orderBy('nombre')->get();

        return response()->json($proveedores);
    }

    /**
     * 🔥 Solo activos (útil para selects)
     * GET /api/proveedores/activos
     */
    public function activos(): JsonResponse
    {
        $proveedores = Proveedor::activos()
            ->orderBy('nombre')
            ->get();

        return response()->json($proveedores);
    }

    // ══════════════════════════════════════════════════════════
    // CREAR
    // ══════════════════════════════════════════════════════════

    /**
     * POST /api/proveedores
     */
    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'ruc'       => ['required', 'digits:11', 'unique:proveedores,ruc'],
            'nombre'    => ['required', 'string', 'max:150'],
            'telefono'  => ['nullable', 'string', 'max:20'],
            'email'     => ['nullable', 'email', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'estado'    => ['nullable', Rule::in(['activo', 'inactivo'])],
        ]);

        $proveedor = Proveedor::create($data);

        return response()->json([
            'message' => 'Proveedor creado correctamente',
            'data' => $proveedor
        ], 201);
    }

    // ══════════════════════════════════════════════════════════
    // MOSTRAR
    // ══════════════════════════════════════════════════════════

    /**
     * GET /api/proveedores/{id}
     */
    public function show(int $id): JsonResponse
    {
        $proveedor = Proveedor::findOrFail($id);

        return response()->json($proveedor);
    }

    // ══════════════════════════════════════════════════════════
    // ACTUALIZAR
    // ══════════════════════════════════════════════════════════

    /**
     * PUT /api/proveedores/{id}
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $proveedor = Proveedor::findOrFail($id);

        $data = $request->validate([
            'ruc' => [
                'required',
                'digits:11',
                Rule::unique('proveedores', 'ruc')->ignore($proveedor->proveedor_id, 'proveedor_id')
            ],
            'nombre'    => ['required', 'string', 'max:150'],
            'telefono'  => ['nullable', 'string', 'max:20'],
            'email'     => ['nullable', 'email', 'max:100'],
            'direccion' => ['nullable', 'string', 'max:200'],
            'estado'    => ['nullable', Rule::in(['activo', 'inactivo'])],
        ]);

        $proveedor->update($data);

        return response()->json([
            'message' => 'Proveedor actualizado correctamente',
            'data' => $proveedor
        ]);
    }

    // ══════════════════════════════════════════════════════════
    // ELIMINAR
    // ══════════════════════════════════════════════════════════

    /**
     * DELETE /api/proveedores/{id}
     */
    public function destroy(int $id): JsonResponse
    {
        $proveedor = Proveedor::findOrFail($id);

        // 🔥 Soft delete lógico recomendado (pero aquí es físico)
        $proveedor->delete();

        return response()->json([
            'message' => 'Proveedor eliminado correctamente'
        ]);
    }
}
