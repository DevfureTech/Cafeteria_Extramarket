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
        Schema::table('alertas', function (Blueprint $table) {
            $table->foreign(['producto_id'], 'alertas_ibfk_1')->references(['producto_id'])->on('productos')->onUpdate('no action')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('alertas', function (Blueprint $table) {
            $table->dropForeign('alertas_ibfk_1');
        });
    }
};
