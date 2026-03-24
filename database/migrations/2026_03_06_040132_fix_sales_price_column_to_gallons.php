<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;

return new class extends Migration
{
    private const LITERS_PER_GALLON = 3.785411784;

    public function up(): void
    {
        DB::transaction(function () {
            Schema::table('sales', function (Blueprint $table) {
                $table->decimal('price_per_gallon', 10, 4)->nullable()->after('fuel_id');
            });

            DB::statement('
                UPDATE sales
                SET price_per_gallon = ROUND((price_per_liter * '.self::LITERS_PER_GALLON.')::numeric, 4)
            ');

            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('price_per_liter');
            });
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            Schema::table('sales', function (Blueprint $table) {
                $table->decimal('price_per_liter', 10, 4)->nullable()->after('fuel_id');
            });

            DB::statement('
                UPDATE sales
                SET price_per_liter = ROUND((price_per_gallon / '.self::LITERS_PER_GALLON.')::numeric, 4)
            ');

            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('price_per_gallon');
            });
        });
    }
};