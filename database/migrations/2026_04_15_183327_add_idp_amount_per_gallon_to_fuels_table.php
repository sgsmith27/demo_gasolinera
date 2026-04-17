<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fuels', function (Blueprint $table) {
            $table->decimal('idp_amount_per_gallon', 12, 4)
                ->default(0)
                ->after('name');
        });
    }

    public function down(): void
    {
        Schema::table('fuels', function (Blueprint $table) {
            $table->dropColumn('idp_amount_per_gallon');
        });
    }
};
