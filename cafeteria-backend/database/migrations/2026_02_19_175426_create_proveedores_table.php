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
        Schema::create('proveedores', function (Blueprint $table) {
            $table->integer('proveedor_id', true);
            $table->string('ruc', 11)->unique('ruc');
            $table->string('nombre', 150);
            $table->string('telefono', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('direccion', 200)->nullable();
            $table->enum('estado', ['activo', 'inactivo'])->nullable()->default('activo');
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
        Schema::dropIfExists('proveedores');
    }
};
