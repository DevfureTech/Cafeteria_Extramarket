<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Proveedor;
use App\Models\MovimientoInventario;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;

class InventarioController extends Controller
{
    // RF-INV-003: Registrar entrada de inventario
    public function registrarEntrada(Request $request): JsonResponse
    {
        $request->validate([
            'producto_id'     => 'required|exists:productos,producto_id',
            'proveedor_id'    => 'required|exists:proveedores,proveedor_id',
            'cantidad'        => 'required|numeric|min:0.01',
            'precio_unitario' => 'required|numeric|min:0',
            'fecha'           => 'nullable|date',
            'motivo'          => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $producto = Producto::findOrFail($request->producto_id);
            $proveedor = Proveedor::findOrFail($request->proveedor_id);

            // Actualizar stock del producto
            $producto->increment('cantidad_actual', $request->cantidad);
            // Actualizar precio de compra si se especifica
            if ($request->precio_unitario > 0) {
                $producto->update(['precio_compra' => $request->precio_unitario]);
            }

            // RF-INV-006: Registrar movimiento en historial
            $movimiento = MovimientoInventario::create([
    'producto_id'     => $request->producto_id,
    'proveedor_id'    => $request->proveedor_id,
    'tipo'            => 'entrada',
    'cantidad'        => $request->cantidad,
    'precio_unitario' => $request->precio_unitario,
    'usuario_id'      => auth()->id() ?? 1,
    'fecha_movimiento'=> $request->fecha ?? now(),
    'motivo'          => $request->motivo ?? 'Entrada de inventario',
            ]);


            DB::commit();

            return response()->json([
                'message'    => 'Entrada registrada correctamente',
                'movimiento' => $this->formatMovimiento($movimiento->load('producto', 'usuario')),
                'stock_nuevo'=> (float) $producto->fresh()->cantidad_actual,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al registrar entrada: ' . $e->getMessage()], 500);
        }
    }

    // RF-INV-004: Registrar salida de inventario
    public function registrarSalida(Request $request): JsonResponse
    {
        $request->validate([
            'producto_id' => 'required|exists:productos,producto_id',
            'cantidad'    => 'required|numeric|min:0.01',
            'motivo'      => 'required|in:venta,merma,ajuste',  // RF-INV-004
            'fecha'       => 'nullable|date',
            'descripcion' => 'nullable|string|max:500',
        ]);

        DB::beginTransaction();
        try {
            $producto = Producto::findOrFail($request->producto_id);

            // Verificar stock suficiente
            if ($producto->cantidad_actual < $request->cantidad) {
                return response()->json([
                    'error' => 'Stock insuficiente. Disponible: ' . $producto->cantidad_actual . ' ' . $producto->unidad_medida
                ], 422);
            }

            // Descontar stock
            $producto->decrement('cantidad_actual', $request->cantidad);

            // RF-INV-006: Registrar movimiento
            $movimiento = MovimientoInventario::create([
                'producto_id'     => $request->producto_id,
                'proveedor_id'    => 1, // PorDefecto, Salida no tiene proveedor
                'tipo'            => 'salida',
                'cantidad'        => $request->cantidad,
                'precio_unitario' => $producto->precio_compra,
                'usuario_id'      => auth()->id(),
                'fecha_movimiento'=> $request->fecha ?? now(),
                'motivo'          => $request->motivo,
                'descripcion'     => $request->descripcion,
            ]);

            DB::commit();

            $stockActual = (float) $producto->fresh()->cantidad_actual;
            // RF-INV-005: Verificar si quedó en stock bajo
            $alertaStock = $stockActual <= $producto->stock_minimo;

            return response()->json([
                'message'     => 'Salida registrada correctamente',
                'movimiento'  => $this->formatMovimiento($movimiento->load('producto', 'usuario')),
                'stock_nuevo' => $stockActual,
                'alerta_stock'=> $alertaStock,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al registrar salida: ' . $e->getMessage()], 500);
        }
    }

    // RF-INV-008: Ajuste manual con justificación obligatoria
    public function registrarAjuste(Request $request): JsonResponse
    {
        $request->validate([
            'producto_id'   => 'required|exists:productos,producto_id',
            'cantidad_nueva'=> 'required|numeric|min:0',
            'justificacion' => 'required|string|min:10|max:1000', // OBLIGATORIA
            'fecha'         => 'nullable|date',
        ]);

        DB::beginTransaction();
        try {
            $producto = Producto::findOrFail($request->producto_id);

$cantidadAnterior = $producto->cantidad_actual; // ✅ GUARDAR ANTES

$diferencia = $request->cantidad_nueva - $cantidadAnterior;
$tipoAjuste = $diferencia >= 0 ? 'ajuste_positivo' : 'ajuste_negativo';

// Actualizar stock al valor exacto indicado
$producto->update([
    'cantidad_actual' => $request->cantidad_nueva
]);

$movimiento = MovimientoInventario::create([
    'producto_id'       => $request->producto_id,
    'proveedor_id'      => 1,
    'tipo'              => 'ajuste',
    'cantidad'          => abs($diferencia),
    'precio_unitario'   => $producto->precio_compra,
    'usuario_id'        => auth()->id(),
    'fecha_movimiento'  => $request->fecha ?? now(),
    'motivo'            => $tipoAjuste,
    'justificacion'     => $request->justificacion,
    'cantidad_anterior' => $cantidadAnterior, // ✅ correcto
    'cantidad_nueva'    => $request->cantidad_nueva,
]);


            DB::commit();

            return response()->json([
                'message'    => 'Ajuste registrado correctamente',
                'movimiento' => $this->formatMovimiento($movimiento->load('producto', 'usuario')),
                'diferencia' => $diferencia,
            ], 201);

        } catch (\Exception $e) {
            DB::rollBack();
            return response()->json(['error' => 'Error al registrar ajuste: ' . $e->getMessage()], 500);
        }
    }

    // RF-INV-006: Historial completo de movimientos
    public function historial(Request $request): JsonResponse
    {
        $query = MovimientoInventario::with(['producto', 'usuario', 'proveedor'])
            ->orderBy('fecha_movimiento', 'desc');

        if ($request->filled('producto_id')) {
            $query->where('producto_id', $request->producto_id);
        }
        if ($request->filled('tipo')) {
            $query->where('tipo', $request->tipo);
        }
        if ($request->filled('desde')) {
            $query->whereDate('fecha_movimiento', '>=', $request->desde);
        }
        if ($request->filled('hasta')) {
            $query->whereDate('fecha_movimiento', '<=', $request->hasta);
        }

        $movimientos = $query->paginate($request->per_page ?? 50);

        return response()->json([
            'data'         => collect($movimientos->items())->map(fn($m) => $this->formatMovimiento($m)),
            'total'        => $movimientos->total(),
            'current_page' => $movimientos->currentPage(),
            'last_page'    => $movimientos->lastPage(),
        ]);
    }

    // Resumen general del inventario
    public function resumen(): JsonResponse
{
    try {
        $totalProductos  = Producto::count();

        $valorTotal = Producto::selectRaw(
            'SUM(cantidad_actual * precio_compra) as valor'
        )->value('valor') ?? 0;

        $stockBajo = Producto::whereColumn(
            'cantidad_actual',
            '<=',
            'stock_minimo'
        )->count();

        $porVencer = Producto::whereNotNull('fecha_vencimiento')
            ->whereDate('fecha_vencimiento', '<=', now()->addDays(7))
            ->count();

        return response()->json([
            'totalProductos' => $totalProductos,
            'valorTotal'     => (float) $valorTotal,
            'stockBajo'      => $stockBajo,
            'porVencer'      => $porVencer,
        ]);

    } catch (\Exception $e) {
        return response()->json([
            'error' => $e->getMessage()
        ], 500);
    }
}



    // Últimos N movimientos para el dashboard
    public function ultimosMovimientos(Request $request): JsonResponse
    {
        $limite = $request->limite ?? 10;
        $movimientos = MovimientoInventario::with(['producto', 'usuario'])
            ->orderBy('fecha_movimiento', 'desc')
            ->limit($limite)
            ->get()
            ->map(fn($m) => $this->formatMovimiento($m));

        return response()->json($movimientos);
    }

    private function formatMovimiento(MovimientoInventario $m): array
    {
        return [
            'id'                => $m->mov_inv_id,
            'producto_id'       => $m->producto_id,
            'proveedor_id'      => $m->proveedor_id,
            'producto_nombre'   => $m->producto?->nombre,
            'producto_codigo'   => $m->producto?->codigo,
            'unidad_medida'     => $m->producto?->unidad_medida,
            'tipo'              => $m->tipo,
            'cantidad'          => (float) $m->cantidad,
            'precio_unitario'   => (float) $m->precio_unitario,
            'usuario_id'        => $m->usuario_id,
            'usuario_nombre'    => $m->usuario?->nombre_completo
                                ?? $m->usuario?->name
                                ?? 'Sistema',
            'fecha_movimiento'  => $m->fecha_movimiento?->format('Y-m-d H:i:s'),
            'motivo'            => $m->motivo,
            'justificacion'     => $m->justificacion,
            'descripcion'       => $m->descripcion,
            'cantidad_anterior' => isset($m->cantidad_anterior) ? (float) $m->cantidad_anterior : null,
            'cantidad_nueva'    => isset($m->cantidad_nueva)    ? (float) $m->cantidad_nueva    : null,
        ];
    }
}
