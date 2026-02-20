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
        Schema::create('rol', function (Blueprint $table) {
            $table->integer('id_rol', true);
            $table->string('nombre', 20); 
            $table->unique('nombre', 'rol_nombre_unique');
            $table->text('descripcion')->nullable();
            $table->dateTime('fecha_creacion')->nullable()->useCurrent();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('rol');
    }
};
