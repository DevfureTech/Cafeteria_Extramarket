<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::connection('pgsql')->create('venta', function (Blueprint $table) {

            $table->bigIncrements('id_venta');

            $table->unsignedBigInteger('id_usuario');

            $table->string('canal', 20);

            $table->string('numero_ticket', 20)->unique();

            $table->decimal('subtotal', 10, 2);
            $table->decimal('igv', 10, 2);
            $table->decimal('total', 10, 2);

            $table->enum('metodo_pago', [
                'EFECTIVO',
                'TARJETA',
                'TRANSFERENCIA'
            ]);

            $table->enum('estado', [
                'COMPLETADA',
                'CANCELADA'
            ]);

            $table->text('motivo_cancelacion')->nullable();

            $table->timestamp('fecha_creacion')->useCurrent();
            $table->timestamp('fecha_actualizacion')->useCurrent();

            $table->foreign('id_usuario')
                  ->references('id_usuario')
                  ->on('usuario');
        });
    }

    public function down(): void
    {
        Schema::connection('pgsql')->dropIfExists('venta');
    }
};