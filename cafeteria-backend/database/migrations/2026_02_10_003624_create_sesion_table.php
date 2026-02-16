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
        Schema::create('sesion', function (Blueprint $table) {
            $table->integer('id_sesion', true);
            $table->integer('id_usuario');
            $table->index('id_usuario', 'sesion_id_usuario_index');

            $table->string('token', 150);
            $table->dateTime('fecha_inicio')->nullable()->useCurrent();
            $table->dateTime('fecha_fin')->nullable();
            $table->string('ip', 50)->nullable();
            $table->boolean('activo')->nullable()->default(false);
            $table->string('user_agent')->nullable();
            $table->dateTime('fecha_expiracion')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sesion');
    }
};
