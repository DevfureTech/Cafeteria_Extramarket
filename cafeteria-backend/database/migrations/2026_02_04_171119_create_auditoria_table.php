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
        Schema::create('auditoria', function (Blueprint $table) {
            $table->integer('id_auditoria', true);
            $table->integer('id_usuario')->index('id_usuario');
            $table->string('tabla', 50)->nullable();
            $table->string('operacion', 20)->nullable();
            $table->string('usuario', 20)->nullable();
            $table->string('ip', 50)->nullable();
            $table->dateTime('fecha')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('auditoria');
    }
};
