<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ProductoController;
use App\Http\Controllers\CategoriaController;
use App\Http\Controllers\InventarioController;
use App\Http\Controllers\ProveedorController;
use App\Http\Controllers\ReporteController;
use App\Http\Controllers\VentaController;


Route::post('/login', [AuthController::class, 'login']);

Route::middleware('check.auth')->group(function () {
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);
    
    // Listar usuarios (cualquier autenticado)
    Route::get('/usuarios', [UserController::class, 'index']);
    
    // Solo administradores pueden crear, editar, eliminar
    Route::middleware('check.role:Administrador')->group(function () {
        Route::post('/usuarios', [UserController::class, 'store']);
        Route::get('/usuarios/{id}', [UserController::class, 'show']);
        Route::put('/usuarios/{id}', [UserController::class, 'update']);
        Route::delete('/usuarios/{id}', [UserController::class, 'destroy']);
        Route::get('/usuarios/auditoria/{id}', [UserController::class, 'auditoria']);


    });

    Route::middleware('auth:sanctum')->group(function () {
    Route::delete('/usuarios/{id}', [UserController::class, 'destroy']);
    });
});

Route::middleware('auth:sanctum')->group(function () {
    // Productos
    Route::get('/categorias',        [CategoriaController::class, 'index']);
    Route::get('/productos/alertas-stock',     [ProductoController::class, 'alertasStock']);
    Route::get('/productos/alertas-vencimiento',[ProductoController::class,'alertasVencimiento']);
    Route::apiResource('productos', ProductoController::class);

    // Inventario
    Route::post('/inventario/entradas',        [InventarioController::class, 'registrarEntrada']);
    Route::post('/inventario/salidas',         [InventarioController::class, 'registrarSalida']);
    Route::post('/inventario/ajustes',         [InventarioController::class, 'registrarAjuste']);
    Route::get('/inventario/historial',        [InventarioController::class, 'historial']);
    Route::get('/inventario/resumen',          [InventarioController::class, 'resumen']);
    Route::get('/inventario/ultimos-movimientos',[InventarioController::class,'ultimosMovimientos']);

    // Reportes
    Route::prefix('reportes')->middleware('auth:sanctum')->group(function () {
    
    // Ventas
    Route::get('/ventas', [ReporteController::class, 'ventasPorPeriodo']);
    
    // Productos
    Route::get('/productos/ranking', [ReporteController::class, 'rankingProductos']);
    Route::get('/productos/baja-rotacion', [ReporteController::class, 'productosBajaRotacion']);
    
    // Inventario
    Route::get('/inventario', [ReporteController::class, 'reporteInventario']);
    
    // Exportación
    Route::post('/exportar/pdf', [ReporteController::class, 'exportarPDF']);
    Route::post('/exportar/excel', [ReporteController::class, 'exportarExcel']);
    });

    // Punto de Venta (POS)
    Route::prefix('pos')->middleware('auth:sanctum')->group(function () {
    
    // Productos
    Route::get('/productos', [VentaController::class, 'obtenerProductos']);
    Route::get('/productos/buscar', [VentaController::class, 'buscarProductos']);
    
    // Ventas
    Route::post('/ventas', [VentaController::class, 'procesarVenta']);
    Route::get('/ventas/hoy', [VentaController::class, 'ventasHoy']);
    Route::post('/ventas/{id}/cancelar', [VentaController::class, 'cancelarVenta']);
});
});


Route::middleware('auth:sanctum')->group(function () {
    // Proveedores
Route::apiResource('proveedores', ProveedorController::class);
Route::get('proveedores-activos', [ProveedorController::class, 'activos']);

});