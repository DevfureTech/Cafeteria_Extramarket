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
        Schema::create('movimientos_inventario', function (Blueprint $table) {
            $table->integer('mov_inv_id', true);
            $table->integer('producto_id'); 
            $table->index('producto_id', 'movimientos_inventario_producto_id_index');
            $table->enum('tipo', ['entrada', 'salida', 'ajuste']);
            $table->decimal('cantidad', 10);
            $table->decimal('precio_unitario', 10)->nullable();
            $table->integer('proveedor_id')->index();
            $table->string('motivo', 50)->nullable();
            $table->text('justificacion')->nullable();
            $table->integer('usuario_id');
            $table->dateTime('fecha_movimiento')->nullable()->useCurrent();
            $table->timestamp('created_at')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('movimientos_inventario');
    }
};
