<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->foreign(['categoria_id'], 'productos_ibfk_1')->references(['categoria_id'])->on('categorias')->onUpdate('no action')->onDelete('restrict');
            $table->foreign(['proveedor_id'], 'productos_ibfk_2')->references(['proveedor_id'])->on('proveedores')->onUpdate('no action')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('productos', function (Blueprint $table) {
            $table->dropForeign('productos_ibfk_1');
            $table->dropForeign('productos_ibfk_2');
        });
    }
};
