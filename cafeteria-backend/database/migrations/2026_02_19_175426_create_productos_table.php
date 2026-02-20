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
        Schema::create('productos', function (Blueprint $table) {
            $table->integer('producto_id', true);
            $table->string('codigo', 50)->unique('codigo');
            $table->string('nombre', 100);
            $table->integer('categoria_id')->index('categoria_id');
            $table->integer('proveedor_id')->nullable()->index('proveedor_id');
            $table->decimal('cantidad_actual', 10)->nullable()->default(0);
            $table->string('unidad_medida', 20);
            $table->decimal('precio_compra', 10)->nullable()->default(0);
            $table->decimal('stock_minimo', 10)->nullable()->default(0);
            $table->dateTime('fecha_vencimiento')->nullable();
            $table->dateTime('fecha_registro')->nullable()->useCurrent();
            $table->timestamp('created_at')->nullable()->useCurrent();
            $table->timestamp('updated_at')->useCurrentOnUpdate()->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('productos');
    }
};
