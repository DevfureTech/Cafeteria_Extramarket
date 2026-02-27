<?php
namespace App\Http\Controllers;
use Maatwebsite\Excel\Facades\Excel;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use App\Exports\VentasExport;
use App\Exports\ProductosExport;
use App\Exports\InventarioExport;
class ReporteController extends Controller
{
    // RF-03: Reporte de ventas por período
    // GET /api/reportes/ventas?periodo=hoy|semana|mes
    public function ventasPorPeriodo(Request $request)
    {
        $periodo = $request->query('periodo', 'hoy');
        
        $fechas = $this->calcularRangoFechas($periodo);
        
        try {
            // Total de ventas
            $totalVentas = DB::table('venta')
                ->whereBetween('fecha_creacion', [$fechas['inicio'], $fechas['fin']])
                ->where('estado', 'COMPLETADA')
                ->sum('total');
            
            // Cantidad de ventas
            $cantidadVentas = DB::table('venta')
                ->whereBetween('fecha_creacion', [$fechas['inicio'], $fechas['fin']])
                ->where('estado', 'COMPLETADA')
                ->count();
            
            // Ticket promedio
            $ticketPromedio = $cantidadVentas > 0 ? $totalVentas / $cantidadVentas : 0;
            
            //Datos para gráfico de ventas por día
            $ventasPorDia = DB::table('venta')
                ->select(
                    DB::raw('DATE(fecha_creacion) as fecha'),
                    DB::raw('SUM(total) as total'),
                    DB::raw('COUNT(*) as cantidad')
                )
                ->whereBetween('fecha_creacion', [$fechas['inicio'], $fechas['fin']])
                ->where('estado', 'COMPLETADA')
                ->groupBy(DB::raw('DATE(fecha_creacion)'))
                ->orderBy('fecha', 'asc')
                ->get();
            
            // Distribución por método de pago
            $distribucionPago = DB::table('venta')
                ->select('metodo_pago', DB::raw('SUM(total) as total'))
                ->whereBetween('fecha_creacion', [$fechas['inicio'], $fechas['fin']])
                ->where('estado', 'COMPLETADA')
                ->groupBy('metodo_pago')
                ->get();
            
            return response()->json([
                'success' => true,
                'periodo' => $periodo,
                'rango_fechas' => $fechas,
                'resumen' => [
                    'total_ventas' => round($totalVentas, 2),
                    'cantidad_ventas' => $cantidadVentas,
                    'ticket_promedio' => round($ticketPromedio, 2),
                ],
                'ventas_por_dia' => $ventasPorDia,
                'distribucion_pago' => $distribucionPago,
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte de ventas',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Ranking de productos más vendidos
     * GET /api/reportes/productos/ranking?limite=10&periodo=hoy|semana|mes
     */
    public function rankingProductos(Request $request)
    {
        $limite = $request->query('limite', 10);
        $periodo = $request->query('periodo', 'mes');
        $fechas = $this->calcularRangoFechas($periodo);
        
        try {
            $ranking = DB::table('detalle_venta')
                ->join('venta', 'detalle_venta.id_venta', '=', 'venta.id_venta')
                ->join('productos', 'detalle_venta.id_producto', '=', 'productos.producto_id')
                ->select(
                    'productos.producto_id',
                    'productos.nombre',
                    'productos.categoria_id',
                    DB::raw('SUM(detalle_venta.cantidad) as cantidad_vendida'),
                    DB::raw('SUM(detalle_venta.subtotal_detalle) as total_ventas')
                )
                ->whereBetween('venta.fecha_creacion', [$fechas['inicio'], $fechas['fin']])
                ->where('venta.estado', 'COMPLETADA')
                ->groupBy('productos.producto_id', 'productos.nombre', 'productos.categoria_id')
                ->orderBy('cantidad_vendida', 'desc')
                ->limit($limite)
                ->get();
            
            return response()->json([
                'success' => true,
                'periodo' => $periodo,
                'ranking' => $ranking
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar ranking de productos',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Productos con baja rotación
     * GET /api/reportes/productos/baja-rotacion?dias=30
     */
    public function productosBajaRotacion(Request $request)
    {
        $dias = $request->query('dias', 30);
        $fechaLimite = Carbon::now()->subDays($dias);
        
        try {
            // Productos que no se han vendido o se vendieron poco
            $productosBajaRotacion = DB::table('productos')
                ->leftJoin('detalle_venta', 'productos.producto_id', '=', 'detalle_venta.id_producto')
                ->leftJoin('venta', function($join) use ($fechaLimite) {
                    $join->on('detalle_venta.id_venta', '=', 'venta.id_venta')
                         ->where('venta.fecha_creacion', '>=', $fechaLimite)
                         ->where('venta.estado', '=', 'COMPLETADA');
                })
                ->select(
                    'productos.producto_id',
                    'productos.codigo',
                    'productos.nombre',
                    'productos.categoria_id',
                    DB::raw('COALESCE(SUM(detalle_venta.cantidad), 0) as cantidad_vendida')
                )
                ->where('productos.cantidad_actual', '>', 0)
                ->groupBy('productos.producto_id', 'productos.codigo', 'productos.nombre', 'productos.categoria_id')
                ->having(DB::raw('COALESCE(SUM(detalle_venta.cantidad), 0)'), '<', 5) // Menos de 5 ventas
                ->orderBy('cantidad_vendida', 'asc')
                ->get();
            
            return response()->json([
                'success' => true,
                'dias_analisis' => $dias,
                'productos' => $productosBajaRotacion
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al identificar productos con baja rotación',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Reporte de inventario
     * GET /api/reportes/inventario
     */
    public function reporteInventario()
    {
        try {
            // Stock disponible por producto
            $inventario = DB::table('productos')
                ->select(
                    'producto_id',
                    'nombre',
                    'codigo',
                    'categoria_id',
                    'cantidad_actual',
                    'unidad_medida',
                    'stock_minimo',
                    'precio_compra',
                    DB::raw('ROUND(cantidad_actual * precio_compra, 2) as valor_total')
                )
                ->orderBy('categoria_id', 'asc')
                ->orderBy('nombre', 'asc')
                ->get();
            
            // Valor total del inventario
            $valorTotalInventario = $inventario->sum('valor_total');
            
            // Total de productos registrados
            $totalProductos = $inventario->count();
            
            // Valor por categoría (para gráfico)
            $valorPorCategoria = DB::table('productos')
                ->select(
                    'categoria_id',
                    DB::raw('SUM(cantidad_actual * precio_compra) as valor')
                )
                ->groupBy('categoria_id')
                ->get();
            
            // Productos bajo stock mínimo
            $alertasStock = DB::table('productos')
                ->where('cantidad_actual', '<=', DB::raw('stock_minimo'))
                ->count();
            
            return response()->json([
                'success' => true,
                'resumen' => [
                    'valor_total' => round($valorTotalInventario, 2),
                    'total_productos' => $totalProductos,
                    'alertas_stock' => $alertasStock
                ],
                'inventario' => $inventario,
                'valor_por_categoria' => $valorPorCategoria
            ]);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar reporte de inventario',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Exportar a PDF
     * POST /api/reportes/exportar/pdf
     */
  public function exportarPDF(Request $request)
    {
        $tipo = $request->input('tipo'); // ventas, productos, inventario
        $periodo = $request->input('periodo', 'hoy');
        
        try {
            $pdf = null;
            $filename = 'reporte_' . $tipo . '_' . date('YmdHis') . '.pdf';
            
            switch ($tipo) {
                case 'ventas':
                    $datos = $this->obtenerDatosVentas($periodo);
                    $pdf = Pdf::loadView('reportes.ventas-pdf', $datos);
                    break;
                
                case 'productos':
                    $datos = $this->obtenerDatosProductos($periodo);
                    $pdf = Pdf::loadView('reportes.productos-pdf', $datos);
                    break;
                
                case 'inventario':
                    $datos = $this->obtenerDatosInventario();
                    $pdf = Pdf::loadView('reportes.inventario-pdf', $datos);
                    break;
                
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Tipo de reporte no válido'
                    ], 400);
            }
            
            // Configurar PDF
            $pdf->setPaper('a4', 'portrait');
            $pdf->setOption('enable-local-file-access', true);
            
            // Retornar PDF directamente para descarga
            return $pdf->download($filename);
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar PDF',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    
    /**
     * Exportar a Excel
     * POST /api/reportes/exportar/excel
     */
public function exportarExcel(Request $request)
    {
        $tipo = $request->input('tipo');
        $periodo = $request->input('periodo', 'hoy');
        
        try {
            $filename = 'reporte_' . $tipo . '_' . date('YmdHis') . '.xlsx';
            
            switch ($tipo) {
                case 'ventas':
                    $datos = $this->obtenerDatosVentas($periodo);
                    return Excel::download(
                        new VentasExport($datos, $periodo),
                        $filename
                    );
                
                case 'productos':
                    $ranking = $this->obtenerRanking($periodo);
                    $bajaRotacion = $this->obtenerBajaRotacion();
                    return Excel::download(
                        new ProductosExport($ranking, $bajaRotacion),
                        $filename
                    );
                
                case 'inventario':
                    $datos = $this->obtenerDatosInventario();
                    return Excel::download(
                        new InventarioExport($datos),
                        $filename
                    );
                
                default:
                    return response()->json([
                        'success' => false,
                        'message' => 'Tipo de reporte no válido'
                    ], 400);
            }
            
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error al generar Excel',
                'error' => $e->getMessage()
            ], 500);
        }
    }
    

    private function obtenerDatosVentas($periodo)
    {
        $fechas = $this->calcularRangoFechas($periodo);
        
        $totalVentas = DB::table('venta')
            ->whereBetween('fecha_creacion', [$fechas['inicio'], $fechas['fin']])
            ->where('estado', 'COMPLETADA')
            ->sum('total');
        
        $cantidadVentas = DB::table('venta')
            ->whereBetween('fecha_creacion', [$fechas['inicio'], $fechas['fin']])
            ->where('estado', 'COMPLETADA')
            ->count();
        
        $ticketPromedio = $cantidadVentas > 0 ? $totalVentas / $cantidadVentas : 0;
        
        $ventasPorDia = DB::table('venta')
            ->select(
                DB::raw('DATE(fecha_creacion) as fecha'),
                DB::raw('SUM(total) as total'),
                DB::raw('COUNT(*) as cantidad')
            )
            ->whereBetween('fecha_creacion', [$fechas['inicio'], $fechas['fin']])
            ->where('estado', 'COMPLETADA')
            ->groupBy(DB::raw('DATE(fecha_creacion)'))
            ->orderBy('fecha', 'asc')
            ->get();
        
        $distribucionPago = DB::table('venta')
            ->select('metodo_pago', DB::raw('SUM(total) as total'))
            ->whereBetween('fecha_creacion', [$fechas['inicio'], $fechas['fin']])
            ->where('estado', 'COMPLETADA')
            ->groupBy('metodo_pago')
            ->get();
        
        return [
            'periodo' => $periodo,
            'rangoFechas' => $fechas,
            'resumen' => [
                'total_ventas' => $totalVentas,
                'cantidad_ventas' => $cantidadVentas,
                'ticket_promedio' => $ticketPromedio,
            ],
            'ventas_por_dia' => $ventasPorDia,
            'distribucion_pago' => $distribucionPago,
        ];
    }
     private function obtenerRanking($periodo)
    {
        $fechas = $this->calcularRangoFechas($periodo);
        
        return DB::table('detalle_venta')
            ->join('venta', 'detalle_venta.id_venta', '=', 'venta.id_venta')
            ->join('producto', 'detalle_venta.id_producto', '=', 'producto.id_producto')
            ->select(
                'producto.id_producto',
                'producto.nombre',
                'producto.categoria',
                DB::raw('SUM(detalle_venta.cantidad) as cantidad_vendida'),
                DB::raw('SUM(detalle_venta.subtotal) as total_ventas')
            )
            ->whereBetween('venta.fecha_creacion', [$fechas['inicio'], $fechas['fin']])
            ->where('venta.estado', 'COMPLETADA')
            ->groupBy('producto.id_producto', 'producto.nombre', 'producto.categoria')
            ->orderBy('cantidad_vendida', 'desc')
            ->limit(10)
            ->get();
    }
    
    /**
     * Helper: Obtener productos con baja rotación
     */
    private function obtenerBajaRotacion()
    {
        $fechaLimite = Carbon::now()->subDays(30);
        
        return DB::table('producto')
            ->leftJoin('detalle_venta', 'producto.id_producto', '=', 'detalle_venta.id_producto')
            ->leftJoin('venta', function($join) use ($fechaLimite) {
                $join->on('detalle_venta.id_venta', '=', 'venta.id_venta')
                     ->where('venta.fecha_creacion', '>=', $fechaLimite)
                     ->where('venta.estado', '=', 'COMPLETADA');
            })
            ->select(
                'producto.id_producto',
                'producto.codigo',
                'producto.nombre',
                'producto.categoria',
                DB::raw('COALESCE(SUM(detalle_venta.cantidad), 0) as cantidad_vendida')
            )
            ->where('producto.disponible', true)
            ->groupBy('producto.id_producto', 'producto.codigo', 'producto.nombre', 'producto.categoria')
            ->having(DB::raw('COALESCE(SUM(detalle_venta.cantidad), 0)'), '<', 5)
            ->orderBy('cantidad_vendida', 'asc')
            ->get();
    }
    
    /**
     * Helper: Obtener datos completos de productos
     */
    private function obtenerDatosProductos($periodo)
    {
        return [
            'periodo' => $periodo,
            'ranking' => $this->obtenerRanking($periodo),
            'bajaRotacion' => $this->obtenerBajaRotacion(),
        ];
    }
    
    /**
     * Helper: Obtener datos de inventario
     */
    private function obtenerDatosInventario()
    {
        $inventario = DB::table('insumo')
            ->select(
                'id_insumo',
                'nombre',
                'codigo',
                'categoria',
                'stock_actual',
                'unidad_medida',
                'stock_minimo',
                'precio_compra',
                DB::raw('ROUND(stock_actual * precio_compra, 2) as valor_total')
            )
            ->orderBy('categoria', 'asc')
            ->orderBy('nombre', 'asc')
            ->get();
        
        $valorTotalInventario = $inventario->sum('valor_total');
        $totalProductos = $inventario->count();
        
        $valorPorCategoria = DB::table('insumo')
            ->select(
                'categoria',
                DB::raw('SUM(stock_actual * precio_compra) as valor')
            )
            ->groupBy('categoria')
            ->get();
        
        $alertasStock = DB::table('insumo')
            ->where('stock_actual', '<=', DB::raw('stock_minimo'))
            ->count();
        
        return [
            'resumen' => [
                'valor_total' => $valorTotalInventario,
                'total_productos' => $totalProductos,
                'alertas_stock' => $alertasStock
            ],
            'inventario' => $inventario,
            'valorPorCategoria' => $valorPorCategoria
        ];
    }

    //Funcion auxiliar para calcular rango de fechas según el periodo seleccionado  
    private function calcularRangoFechas($periodo)
    {
        $now = Carbon::now();
        
        switch ($periodo) {
            case 'hoy':
                return [
                    'inicio' => $now->copy()->startOfDay(),
                    'fin' => $now->copy()->endOfDay(),
                ];
            
            case 'semana':
                return [
                    'inicio' => $now->copy()->startOfWeek(),
                    'fin' => $now->copy()->endOfWeek(),
                ];
            
            case 'mes':
                return [
                    'inicio' => $now->copy()->startOfMonth(),
                    'fin' => $now->copy()->endOfMonth(),
                ];
            
            case 'ultima_semana':
                return [
                    'inicio' => $now->copy()->subWeek()->startOfWeek(),
                    'fin' => $now->copy()->subWeek()->endOfWeek(),
                ];
            
            case 'ultimo_mes':
                return [
                    'inicio' => $now->copy()->subMonth()->startOfMonth(),
                    'fin' => $now->copy()->subMonth()->endOfMonth(),
                ];
            
            default:
                return [
                    'inicio' => $now->copy()->startOfDay(),
                    'fin' => $now->copy()->endOfDay(),
                ];
        }
    }



}