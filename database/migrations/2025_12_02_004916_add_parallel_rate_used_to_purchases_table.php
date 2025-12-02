<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->decimal('parallel_rate_used', 10, 2)->nullable()->after('total_amount');
            $table->decimal('real_total_bcv', 10, 2)->nullable()->after('parallel_rate_used');
        });
    }

    public function down()
    {
        Schema::table('purchases', function (Blueprint $table) {
            $table->dropColumn(['parallel_rate_used', 'real_total_bcv']);
        });
    }
};
