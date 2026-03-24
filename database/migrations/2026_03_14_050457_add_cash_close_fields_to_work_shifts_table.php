<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_shifts', function (Blueprint $table) {
            $table->decimal('expected_cash_q', 12, 2)->nullable()->after('opening_cash_q');
            $table->decimal('delivered_cash_q', 12, 2)->nullable()->after('expected_cash_q');
            $table->decimal('cash_difference_q', 12, 2)->nullable()->after('delivered_cash_q');
        });
    }

    public function down(): void
    {
        Schema::table('work_shifts', function (Blueprint $table) {
            $table->dropColumn([
                'expected_cash_q',
                'delivered_cash_q',
                'cash_difference_q',
            ]);
        });
    }
};