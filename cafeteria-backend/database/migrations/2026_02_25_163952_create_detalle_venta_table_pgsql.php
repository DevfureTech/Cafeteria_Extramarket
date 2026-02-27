<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('pgsql')->create('detalle_venta', function (Blueprint $table) {

            $table->bigIncrements('id_detalle');

            $table->unsignedBigInteger('id_venta');
            $table->unsignedBigInteger('id_producto')->nullable();

            $table->integer('cantidad');
            $table->decimal('precio_unitario', 10, 2);
            $table->decimal('subtotal_detalle', 10, 2);

            $table->timestamp('fecha_creacion')->useCurrent();

            $table->foreign('id_venta')
                  ->references('id_venta')
                  ->on('venta')
                  ->onDelete('cascade');

            $table->foreign('id_producto')
                  ->references('producto_id')
                  ->on('productos');
        });
    }

    public function down(): void
    {
        Schema::connection('pgsql')->dropIfExists('detalle_venta');
    }
};