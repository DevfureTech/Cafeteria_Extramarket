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
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->foreign(['producto_id'], 'movimientos_inventario_ibfk_1')->references(['producto_id'])->on('productos')->onUpdate('no action')->onDelete('cascade');
            $table->foreign(['proveedor_id'], 'movimientos_inventario_ibfk_2')->references(['proveedor_id'])->on('proveedores')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('movimientos_inventario', function (Blueprint $table) {
            $table->dropForeign('movimientos_inventario_ibfk_1');
            $table->dropForeign('movimientos_inventario_ibfk_2');
        });
    }
};
