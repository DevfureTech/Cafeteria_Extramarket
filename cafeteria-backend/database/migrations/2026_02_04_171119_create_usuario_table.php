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
        Schema::create('usuario', function (Blueprint $table) {
            $table->integer('id_usuario', true);
            $table->integer('id_rol')->index('id_rol');
            $table->string('nombre_completo', 150);
            $table->string('nombre_usuario', 20);
            $table->string('contraseña_administrador', 150)->unique('contraseña_administrador');
            $table->char('pin_usuario', 4);
            $table->boolean('activo')->default(false);
            $table->dateTime('fecha_creacion')->nullable()->useCurrent();
            $table->dateTime('fecha_actualizacion')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('usuario');
    }
};
