<?php

namespace App\Http\Controllers;

use App\Models\Producto;
use App\Models\Categoria;
use Illuminate\Http\JsonResponse;

class LandingController extends Controller
{
    /**
     * Obtener productos para mostrar en landing page (sin autenticación)
     * Solo muestra productos activos con stock disponible
     */
    public function getProductosPublicos(): JsonResponse
    {
        $productos = Producto::with(['categoria', 'proveedor'])
            ->where('cantidad_actual', '>', 0)
            ->orderBy('nombre')
            ->limit(20) // Limitar para mejor rendimiento
            ->get()
            ->map(function ($p) {
                return [
                    'producto_id'        => $p->producto_id,
                    'codigo'             => $p->codigo,
                    'nombre'             => $p->nombre,
                    'categoria_id'       => $p->categoria_id,
                    'categoria_nombre'   => $p->categoria?->nombre,
                    'cantidad_actual'    => (float) $p->cantidad_actual,
                    'unidad_medida'      => $p->unidad_medida,
                    'precio_compra'      => (float) $p->precio_compra,
                    'proveedor_nombre'   => $p->proveedor?->nombre,
                    'stock_minimo'       => (float) $p->stock_minimo,
                ];
            });

        return response()->json($productos);
    }

    /**
     * Obtener estadísticas generales para landing page
     */
    public function getEstadisticasPublicas(): JsonResponse
    {
        $totalProductos = Producto::where('cantidad_actual', '>', 0)->count();
        $totalCategorias = Categoria::count();
        
        $valorInventario = Producto::where('cantidad_actual', '>', 0)
            ->selectRaw('SUM(cantidad_actual * precio_compra) as valor')
            ->value('valor') ?? 0;
        
        $alertasStock = Producto::stockBajo()
            ->where('cantidad_actual', '>', 0)
            ->count();

        return response()->json([
            'total_productos' => $totalProductos,
            'total_categorias' => $totalCategorias,
            'valor_inventario' => (float) $valorInventario,
            'alertas_stock' => $alertasStock,
        ]);
    }
}