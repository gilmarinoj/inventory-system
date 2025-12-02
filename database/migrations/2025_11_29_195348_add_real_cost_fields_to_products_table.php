<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->decimal('purchase_price_usd', 12, 4)->nullable()->after('price');
            $table->decimal('purchase_price_bcv', 12, 4)->nullable()->after('purchase_price_usd');
            $table->decimal('last_parallel_rate', 12, 4)->nullable()->after('purchase_price_bcv');
            $table->decimal('last_bcv_rate', 12, 4)->nullable()->after('last_parallel_rate');
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropColumn(['purchase_price_usd', 'purchase_price_bcv', 'last_parallel_rate', 'last_bcv_rate']);
        });
    }
};