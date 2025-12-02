<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->decimal('cost_usd_paid', 12, 4)->nullable();
            $table->decimal('cost_bcv_equivalent', 12, 4)->nullable();
            $table->decimal('parallel_rate_used', 12, 4)->nullable();
            $table->decimal('bcv_rate_used', 12, 4)->nullable();
        });
    }

    public function down(): void
    {
        Schema::table('purchase_items', function (Blueprint $table) {
            $table->dropColumn(['cost_usd_paid', 'cost_bcv_equivalent', 'parallel_rate_used', 'bcv_rate_used']);
        });
    }
};