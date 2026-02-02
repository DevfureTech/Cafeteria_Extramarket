<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\UserController;

//Rutas Públicas

Route::post('/login', [AuthController::class, 'login']);

//Rutas protegidas
Route::middleware(['check.auth', 'check.inactivity'])->group(function () {

    // Auth
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::get('/me', [AuthController::class, 'me']);

//Modulo usuario - admin
    Route::middleware(['check.role:usuarios,leer'])->group(function () {
        Route::get('/usuarios', [UserController::class, 'index']);
        Route::get('/usuarios/{id}', [UserController::class, 'show']);
    });

    Route::middleware(['check.role:usuarios,crear'])->group(function () {
        Route::post('/usuarios', [UserController::class, 'store']);
    });

    Route::middleware(['check.role:usuarios,actualizar'])->group(function () {
        Route::put('/usuarios/{id}', [UserController::class, 'update']);
    });

    Route::middleware(['check.role:usuarios,eliminar'])->group(function () {
        Route::delete('/usuarios/{id}', [UserController::class, 'destroy']);
    });
});
