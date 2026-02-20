<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Models\Categoria;
use Illuminate\Http\Request;

class CategoriaController extends Controller
{
    // ✅ LISTAR
    public function index()
    {
        try {
            $categorias = Categoria::select(
                'categoria_id',
                'nombre',
                'descripcion'
            )
            ->orderBy('nombre')
            ->get();

            return response()->json($categorias);

        } catch (\Throwable $e) {
            return response()->json([
                'message' => 'Error cargando categorías',
                'error' => $e->getMessage()
            ], 500);
        }
    }
}
