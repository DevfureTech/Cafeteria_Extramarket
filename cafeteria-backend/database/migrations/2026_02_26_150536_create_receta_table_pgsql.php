<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
        public function up(): void
    {
        Schema::connection('pgsql')->create('receta', function (Blueprint $table) {

            $table->bigIncrements('id_receta');

            $table->unsignedBigInteger('id_producto');
            $table->unsignedBigInteger('id_inventario')->nullable();

            $table->integer('cantidad');

            $table->foreign('id_producto')
                  ->references('producto_id')
                  ->on('productos')
                  ->onDelete('cascade');

            $table->foreign('id_inventario')
                  ->references('mov_inv_id')
                  ->on('movimientos_inventario');
        });
    }

    public function down(): void
    {
        Schema::connection('pgsql')->dropIfExists('receta');
    }
};
