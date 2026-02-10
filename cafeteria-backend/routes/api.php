<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

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