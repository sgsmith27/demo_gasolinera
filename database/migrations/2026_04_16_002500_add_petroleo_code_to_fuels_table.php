<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fuels', function (Blueprint $table) {
            $table->string('petroleo_code', 10)
                ->nullable()
                ->after('idp_amount_per_gallon');
        });
    }

    public function down(): void
    {
        Schema::table('fuels', function (Blueprint $table) {
            $table->dropColumn('petroleo_code');
        });
    }
};