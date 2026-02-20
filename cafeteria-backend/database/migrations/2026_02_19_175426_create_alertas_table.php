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
        Schema::create('alertas', function (Blueprint $table) {
            $table->integer('alerta_id', true);
            $table->enum('tipo', ['stock_minimo', 'vencimiento']);
            $table->integer('producto_id')->index('producto_id');
            $table->text('mensaje');
            $table->dateTime('fecha_alerta')->nullable()->useCurrent();
            $table->boolean('leida')->nullable()->default(false);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};
