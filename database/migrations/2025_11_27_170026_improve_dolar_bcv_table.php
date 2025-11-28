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
        Schema::table('dolar_bcv', function (Blueprint $table) {
            $table->decimal('tasa', 12, 4)->change(); // 4 decimales mínimo
            $table->unique(['fecha', 'hora']);
            $table->index('fecha');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('dolar_bcv', function (Blueprint $table) {
            //
        });
    }
};
