<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class VentaController extends Controller
{
    /**
     * Obtener productos disponibles para venta
     * GET /api/pos/productos
     */
    public function obtenerProductos()
    {
        try {
            $productos = DB::table('productos')
                ->join('categorias', 'productos.categoria_id', '=', 'categorias.categoria_id')
                ->select(
                    'productos.producto_id',
                    'productos.codigo',
                    'productos.nombre as nombre_producto',
                    DB::raw('productos.precio_compra * 1.5 as precio_venta'),
                    'productos.categoria_id',
                    'categorias.nombre as categoria_nombre',
                    'productos.cantidad_actual as cantidad'
                )
                ->where('productos.cantidad_actual', '>', 0)
                ->orderBy('productos.categoria_id')
                ->orderBy('productos.nombre')
                ->get();
            // Agrupar por categoría
            $productosPorCategoria = $productos->groupBy('categoria_id')->map(function($items, $categoriaId) {
                return [
                    'categoria_id' => $categoriaId,
                    'productos' => $items->values()
                ];
            })->values();
            
            return response()->json([
                'success' => true,
                'productos' => $productos,
                'por_categoria' => $productosPorCategoria
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al cargar productos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Procesar venta completa
     * POST /api/pos/ventas
     */
    public function procesarVenta(Request $request)
    {
        // Validar request
        $validated = $request->validate([
            'items' => 'required|array|min:1',
            'items.*.id_producto' => 'required|integer|exists:productos,producto_id',
            'items.*.nombre' => 'required|string',
            'items.*.cantidad' => 'required|integer|min:1',
            'items.*.precio_unitario' => 'required|numeric|min:0',
            'items.*.subtotal' => 'required|numeric|min:0',
            'metodo_pago' => 'required|string|in:EFECTIVO,TARJETA,TRANSFERENCIA',
            'subtotal' => 'required|numeric|min:0',
            'igv' => 'required|numeric|min:0',
            'total' => 'required|numeric|min:0',
        ], [
            'items.required' => 'Debe agregar al menos un producto',
            'items.*.id_producto.exists' => 'Producto no válido',
            'metodo_pago.required' => 'Debe seleccionar un método de pago',
            'metodo_pago.in' => 'Método de pago no válido'
        ]);
        
        try {
            // Obtener usuario autenticado
            $idUsuario = $request->user()->id_usuario ?? 1;
            
            // Llamar al stored procedure
            $resultado = DB::select(
                'SELECT * FROM procesar_venta_pos(?, ?::jsonb, ?, ?, ?, ?)',
                [
                    $idUsuario,
                    json_encode($validated['items']),
                    $validated['metodo_pago'],
                    $validated['subtotal'],
                    $validated['igv'],
                    $validated['total']
                ]
            );
            
            $resultado = $resultado[0];
            
            if (!$resultado->success) {
                return response()->json([
                    'success' => false,
                    'message' => $resultado->mensaje
                ], 400);
            }
            
            // Obtener detalles completos de la venta para el ticket
            $venta = DB::table('venta')
                ->where('id_venta', $resultado->id_venta_retorno)
                ->first();
            
            $detalles = DB::table('detalle_venta')
                ->where('id_venta', $resultado->id_venta_retorno)
                ->get();
            
            return response()->json([
                'success' => true,
                'message' => 'Venta procesada exitosamente',
                'ticket' => $resultado->ticket,
                'venta' => [
                    'id_venta' => $resultado->id_venta_retorno,
                    'numero_ticket' => $resultado->ticket,
                    'fecha_creacion' => $venta->fecha_creacion,
                    'metodo_pago' => $venta->metodo_pago,
                    'subtotal' => $venta->subtotal,
                    'igv' => $venta->igv,
                    'total' => $venta->total,
                    'detalles' => $detalles
                ]
            ], 201);
            
        } catch (\Illuminate\Validation\ValidationException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Errores de validación',
                'errors' => $e->errors()
            ], 422);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al procesar la venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Cancelar venta
     * POST /api/pos/ventas/{id}/cancelar
     */
    public function cancelarVenta(Request $request, $id)
    {
        $validated = $request->validate([
            'motivo' => 'required|string|min:10|max:500'
        ], [
            'motivo.required' => 'Debe justificar la cancelación',
            'motivo.min' => 'El motivo debe tener al menos 10 caracteres'
        ]);
        
        try {
            DB::beginTransaction();
            
            // Obtener venta
            $venta = DB::table('venta')->where('id_venta', $id)->first();
            
            if (!$venta) {
                return response()->json([
                    'success' => false,
                    'message' => 'Venta no encontrada'
                ], 404);
            }
            
            // Verificar que sea del mismo día
            $fechaVenta = Carbon::parse($venta->fecha_creacion);
            $hoy = Carbon::today();
            
            if (!$fechaVenta->isSameDay($hoy)) {
                return response()->json([
                    'success' => false,
                    'message' => 'Solo se pueden cancelar ventas del día actual'
                ], 400);
            }
            
            // Verificar que no esté ya cancelada
            if ($venta->estado === 'CANCELADA') {
                return response()->json([
                    'success' => false,
                    'message' => 'La venta ya está cancelada'
                ], 400);
            }
            
            // Cancelar venta
            DB::table('venta')
                ->where('id_venta', $id)
                ->update([
                    'estado' => 'CANCELADA',
                    'motivo_cancelacion' => $validated['motivo'],
                    'fecha_cancelacion' => now()
                ]);
            
            // Devolver inventario (revertir descuentos)
            $this->devolverInventario($id);
            
            // Auditoría
            DB::table('auditoria')->insert([
                'id_usuario' => $request->user()->id_usuario ?? 1,
                'tabla' => 'venta',
                'operacion' => 'CANCEL',
                'ip' => $request->ip(),
                'fecha' => now()
            ]);
            
            DB::commit();
            
            return response()->json([
                'success' => true,
                'message' => 'Venta cancelada exitosamente'
            ]);
            
        } catch (\Exception $e) {
            DB::rollBack();
            
            return response()->json([
                'success' => false,
                'message' => 'Error al cancelar la venta',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Buscar productos
     * GET /api/pos/productos/buscar?q=cafe
     */
    public function buscarProductos(Request $request)
    {
        $query = $request->query('q', '');
        
        if (strlen($query) < 2) {
            return response()->json([
                'success' => false,
                'message' => 'Ingrese al menos 2 caracteres para buscar'
            ], 400);
        }
        
        try {
            $productos = DB::table('productos')
                ->where('cantidad_actual', '>', 0)
                ->where(function($q) use ($query) {
                    $q->where('nombre', 'ILIKE', "%{$query}%")
                      ->orWhere('codigo', 'ILIKE', "%{$query}%")
                      ->orWhere('descripcion', 'ILIKE', "%{$query}%");
                })
                ->limit(10)
                ->get();
            
            return response()->json([
                'success' => true,
                'productos' => $productos
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al buscar productos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Obtener ventas del día
     * GET /api/pos/ventas/hoy
     */
    public function ventasHoy()
    {
        try {
            $ventas = DB::table('venta')
                ->whereDate('fecha_creacion', today())
                ->orderBy('fecha_creacion', 'desc')
                ->get();
            
            $totalVentas = $ventas->where('estado', 'COMPLETADA')->sum('total');
            $cantidadVentas = $ventas->where('estado', 'COMPLETADA')->count();
            
            return response()->json([
                'success' => true,
                'ventas' => $ventas,
                'resumen' => [
                    'total' => $totalVentas,
                    'cantidad' => $cantidadVentas,
                    'canceladas' => $ventas->where('estado', 'CANCELADA')->count()
                ]
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al obtener ventas del día',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Devolver inventario al cancelar venta
     */
    private function devolverInventario($idVenta)
    {
        // Obtener detalles de venta
        $detalles = DB::table('detalle_venta')
            ->where('id_venta', $idVenta)
            ->get();
        
        foreach ($detalles as $detalle) {
            // Obtener receta del producto
            $recetas = DB::table('receta')
                ->where('id_producto', $detalle->id_producto)
                ->get();
            foreach ($recetas as $receta) {
                $cantidadDevolver = $receta->cantidad * $detalle->cantidad;
                $stockAnterior = DB::table('productos')
                    ->where('producto_id', $receta->id_producto)
                    ->value('stock_actual');
                
                // Incrementar stock
                DB::table('productos')
                    ->where('producto_id', $receta->id_producto)
                    ->increment('stock_actual', $cantidadDevolver);
                
                // Registrar movimiento
                DB::table('movimiento_inventario')->insert([
                    'id_producto' => $receta->id_producto,
                    'tipo_movimiento' => 'AJUSTE',
                    'cantidad' => $cantidadDevolver,
                    'stock_anterior' => $stockAnterior,
                    'stock_nuevo' => $stockAnterior + $cantidadDevolver,
                    'id_venta' => $idVenta,
                    'motivo' => 'Devolución por cancelación de venta',
                    'fecha' => now()
                ]);
            }
        }
    }
}