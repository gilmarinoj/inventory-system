<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (!Schema::hasColumn('order_items', 'unit_price_usd')) {
                $table->decimal('unit_price_usd', 12, 4)->nullable()->after('price');
            }
        });

        // Solo rellenamos si hay filas y la columna existe
        if (Schema::hasColumn('order_items', 'unit_price_usd')) {
            DB::statement("
                UPDATE order_items 
                SET unit_price_usd = ROUND(price / NULLIF(quantity, 0), 4)
                WHERE quantity > 0
                  AND (unit_price_usd IS NULL OR unit_price_usd = 0)
            ");
        }

        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'unit_price_usd')) {
                $table->decimal('unit_price_usd', 12, 4)->nullable(false)->change();
            }
        });
    }

    public function down(): void
    {
        Schema::table('order_items', function (Blueprint $table) {
            if (Schema::hasColumn('order_items', 'unit_price_usd')) {
                $table->dropColumn('unit_price_usd');
            }
        });
    }
};
