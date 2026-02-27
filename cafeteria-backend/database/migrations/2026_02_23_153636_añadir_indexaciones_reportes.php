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
         // Índices en tabla venta
        Schema::table('venta', function (Blueprint $table) {
            $table->index('fecha_creacion');
            $table->index('estado');
            $table->index(['fecha_creacion', 'estado']);
            $table->index('metodo_pago');
        });
        
        // Índices en tabla detalle_venta
        Schema::table('detalle_venta', function (Blueprint $table) {
            $table->index('id_producto');
            $table->index('id_venta');
        });
        
        // Índices en tabla producto
        Schema::table('productos', function (Blueprint $table) {
            $table->index('categoria_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('venta', function (Blueprint $table) {
            $table->dropIndex(['fecha_creacion']);
            $table->dropIndex(['estado']);
            $table->dropIndex(['fecha_creacion', 'estado']);
            $table->dropIndex(['metodo_pago']);
        });
        
        Schema::table('detalle_venta', function (Blueprint $table) {
            $table->dropIndex(['id_producto']);
            $table->dropIndex(['id_venta']);
        });
        
        Schema::table('productos', function (Blueprint $table) {
            $table->dropIndex(['categoria_id']);
        }); 
    }
};
