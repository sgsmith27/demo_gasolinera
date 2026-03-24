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
            // -----------------------------
            // 1) tanks
            // -----------------------------
            Schema::table('tanks', function (Blueprint $table) {
                $table->decimal('capacity_gallons', 14, 3)->nullable();
                $table->decimal('current_gallons', 14, 3)->default(0);
            });

            DB::statement('
                UPDATE tanks
                SET
                    capacity_gallons = CASE
                        WHEN capacity_liters IS NULL THEN NULL
                        ELSE ROUND((capacity_liters / '.self::LITERS_PER_GALLON.')::numeric, 3)
                    END,
                    current_gallons = ROUND((current_liters / '.self::LITERS_PER_GALLON.')::numeric, 3)
            ');

            Schema::table('tanks', function (Blueprint $table) {
                $table->dropColumn(['capacity_liters', 'current_liters']);
            });

            // -----------------------------
            // 2) sales
            // -----------------------------
            Schema::table('sales', function (Blueprint $table) {
                $table->decimal('gallons', 14, 3)->default(0);
            });

            DB::statement('
                UPDATE sales
                SET gallons = ROUND((liters / '.self::LITERS_PER_GALLON.')::numeric, 3)
            ');

            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('liters');
            });

            // -----------------------------
            // 3) inventory_movements
            // -----------------------------
            Schema::table('inventory_movements', function (Blueprint $table) {
                $table->decimal('gallons_delta', 14, 3)->default(0);
            });

            DB::statement('
                UPDATE inventory_movements
                SET gallons_delta = ROUND((liters_delta / '.self::LITERS_PER_GALLON.')::numeric, 3)
            ');

            Schema::table('inventory_movements', function (Blueprint $table) {
                $table->dropColumn('liters_delta');
            });

            // -----------------------------
            // 4) fuel_deliveries
            // -----------------------------
            Schema::table('fuel_deliveries', function (Blueprint $table) {
                $table->decimal('gallons', 14, 3)->default(0);
            });

            DB::statement('
                UPDATE fuel_deliveries
                SET gallons = ROUND((liters / '.self::LITERS_PER_GALLON.')::numeric, 3)
            ');

            Schema::table('fuel_deliveries', function (Blueprint $table) {
                $table->dropColumn('liters');
            });

            // -----------------------------
            // 5) fuel_prices
            // -----------------------------
            Schema::table('fuel_prices', function (Blueprint $table) {
                $table->decimal('price_per_gallon', 10, 4)->default(0);
            });

            DB::statement('
                UPDATE fuel_prices
                SET price_per_gallon = ROUND((price_per_liter * '.self::LITERS_PER_GALLON.')::numeric, 4)
            ');

            Schema::table('fuel_prices', function (Blueprint $table) {
                $table->dropColumn('price_per_liter');
            });
        });
    }

    public function down(): void
    {
        DB::transaction(function () {
            // -----------------------------
            // 1) tanks
            // -----------------------------
            Schema::table('tanks', function (Blueprint $table) {
                $table->decimal('capacity_liters', 14, 3)->nullable();
                $table->decimal('current_liters', 14, 3)->default(0);
            });

            DB::statement('
                UPDATE tanks
                SET
                    capacity_liters = CASE
                        WHEN capacity_gallons IS NULL THEN NULL
                        ELSE ROUND((capacity_gallons * '.self::LITERS_PER_GALLON.')::numeric, 3)
                    END,
                    current_liters = ROUND((current_gallons * '.self::LITERS_PER_GALLON.')::numeric, 3)
            ');

            Schema::table('tanks', function (Blueprint $table) {
                $table->dropColumn(['capacity_gallons', 'current_gallons']);
            });

            // -----------------------------
            // 2) sales
            // -----------------------------
            Schema::table('sales', function (Blueprint $table) {
                $table->decimal('liters', 14, 3)->default(0);
            });

            DB::statement('
                UPDATE sales
                SET liters = ROUND((gallons * '.self::LITERS_PER_GALLON.')::numeric, 3)
            ');

            Schema::table('sales', function (Blueprint $table) {
                $table->dropColumn('gallons');
            });

            // -----------------------------
            // 3) inventory_movements
            // -----------------------------
            Schema::table('inventory_movements', function (Blueprint $table) {
                $table->decimal('liters_delta', 14, 3)->default(0);
            });

            DB::statement('
                UPDATE inventory_movements
                SET liters_delta = ROUND((gallons_delta * '.self::LITERS_PER_GALLON.')::numeric, 3)
            ');

            Schema::table('inventory_movements', function (Blueprint $table) {
                $table->dropColumn('gallons_delta');
            });

            // -----------------------------
            // 4) fuel_deliveries
            // -----------------------------
            Schema::table('fuel_deliveries', function (Blueprint $table) {
                $table->decimal('liters', 14, 3)->default(0);
            });

            DB::statement('
                UPDATE fuel_deliveries
                SET liters = ROUND((gallons * '.self::LITERS_PER_GALLON.')::numeric, 3)
            ');

            Schema::table('fuel_deliveries', function (Blueprint $table) {
                $table->dropColumn('gallons');
            });

            // -----------------------------
            // 5) fuel_prices
            // -----------------------------
            Schema::table('fuel_prices', function (Blueprint $table) {
                $table->decimal('price_per_liter', 10, 4)->default(0);
            });

            DB::statement('
                UPDATE fuel_prices
                SET price_per_liter = ROUND((price_per_gallon / '.self::LITERS_PER_GALLON.')::numeric, 4)
            ');

            Schema::table('fuel_prices', function (Blueprint $table) {
                $table->dropColumn('price_per_gallon');
            });
        });
    }
};