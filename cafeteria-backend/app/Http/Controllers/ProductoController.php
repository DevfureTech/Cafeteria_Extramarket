<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Validation\Rule;

class ProductoController extends Controller
{
    /**
     * RF-INV-007: Listar productos con filtros
     */
    public function index(Request $request): JsonResponse
    {
        $query = Producto::with(['categoria', 'proveedor']); // ✅ FIX

        // Filtro por nombre
        if ($request->filled('nombre')) {
            $query->where('nombre', 'LIKE', '%' . $request->nombre . '%');
        }

        // Filtro por código
        if ($request->filled('codigo')) {
            $query->where('codigo', 'LIKE', '%' . $request->codigo . '%');
        }

        // Filtro por categoría
        if ($request->filled('categoria_id')) {
            $query->where('categoria_id', $request->categoria_id);
        }

        $productos = $query
            ->orderBy('nombre')
            ->get()
            ->map(fn($p) => $this->formatProducto($p));

        return response()->json($productos);
    }

    /**
     * Registrar producto
     */
    public function store(Request $request): JsonResponse
    {
        $request->validate([
            'codigo'            => 'required|string|max:50|unique:productos,codigo',
            'nombre'            => 'required|string|max:100',
            'categoria_id'      => 'required|exists:categorias,categoria_id',
            'cantidad_actual'   => 'required|numeric|min:0',
            'unidad_medida'     => 'required|string|max:20',
            'precio_compra'     => 'required|numeric|min:0',
            'proveedor_id'      => 'required|exists:proveedores,proveedor_id',
            'stock_minimo'      => 'required|numeric|min:0',
            'fecha_vencimiento' => 'nullable|date|after:today',
        ]);

        $producto = Producto::create($request->all());

        return response()->json(
            $this->formatProducto(
                $producto->load(['categoria', 'proveedor']) // ✅ FIX
            ),
            201
        );
    }

    /**
     * Mostrar producto
     */
    public function show(int $id): JsonResponse
    {
        $producto = Producto::with(['categoria', 'proveedor']) // ✅ FIX
            ->findOrFail($id);

        return response()->json($this->formatProducto($producto));
    }

    /**
     * Actualizar producto
     */
    public function update(Request $request, int $id): JsonResponse
    {
        $producto = Producto::findOrFail($id);

        $request->validate([
            'codigo'            => ['required','string','max:50', Rule::unique('productos','codigo')->ignore($id,'producto_id')],
            'nombre'            => 'required|string|max:100',
            'categoria_id'      => 'required|exists:categorias,categoria_id',
            'cantidad_actual'   => 'required|numeric|min:0',
            'unidad_medida'     => 'required|string|max:20',
            'precio_compra'     => 'required|numeric|min:0',
            'proveedor_id'      => 'required|exists:proveedores,proveedor_id',
            'stock_minimo'      => 'required|numeric|min:0',
            'fecha_vencimiento' => 'nullable|date',
        ]);

        $producto->update($request->all());

        return response()->json(
            $this->formatProducto(
                $producto->load(['categoria', 'proveedor']) // ✅ FIX
            )
        );
    }

    /**
     * Eliminar
     */
    public function destroy(int $id): JsonResponse
    {
        Producto::findOrFail($id)->delete();

        return response()->json(['message' => 'Producto eliminado correctamente']);
    }

    /**
     * Alertas stock bajo
     */
    public function alertasStock(): JsonResponse
    {
        $alertas = Producto::stockBajo()
            ->with(['categoria', 'proveedor']) // ✅ FIX
            ->get()
            ->map(fn($p) => $this->formatProducto($p));

        return response()->json($alertas);
    }

    /**
     * Alertas vencimiento
     */
    public function alertasVencimiento(): JsonResponse
    {
        $alertas = Producto::proximosVencer(7)
            ->with(['categoria', 'proveedor']) // ✅ FIX
            ->get()
            ->map(fn($p) => $this->formatProducto($p));

        return response()->json($alertas);
    }

    /**
     * Categorías
     */
    public function getCategorias(): JsonResponse
    {
        return response()->json(Categoria::all());
    }

    /**
     * Formatear producto
     */
    private function formatProducto(Producto $p): array
    {
        return [
            'producto_id'       => $p->producto_id,
            'id'                => $p->producto_id,
            'codigo'            => $p->codigo,
            'nombre'            => $p->nombre,
            'categoria_id'      => $p->categoria_id,
            'categoria_nombre'  => $p->categoria?->nombre,

            // 🔥 ESTA ES LA CLAVE PARA TU VUE
            'proveedor_id'      => $p->proveedor_id,
            'proveedor_nombre'  => $p->proveedor?->nombre,

            'cantidad_actual'   => (float) $p->cantidad_actual,
            'unidad_medida'     => $p->unidad_medida,
            'precio_compra'     => (float) $p->precio_compra,
            'stock_minimo'      => (float) $p->stock_minimo,
            'fecha_vencimiento' => $p->fecha_vencimiento?->format('Y-m-d'),
            'stock_bajo'        => $p->stock_bajo,
            'dias_para_vencer'  => $p->dias_para_vencer,
            'fecha_registro'    => $p->created_at?->format('Y-m-d H:i:s'),
        ];
    }
}
