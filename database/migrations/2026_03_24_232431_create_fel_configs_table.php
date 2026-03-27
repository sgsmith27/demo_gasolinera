<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('fel_configs', function (Blueprint $table) {
            $table->id();

            $table->string('environment', 20)->default('test'); // test | production

            $table->string('taxid', 20);
            $table->string('username', 100);
            $table->string('password', 255);

            $table->string('seller_name', 255);
            $table->string('seller_address', 255);
            $table->string('afiliacion_iva', 20)->default('GEN');
            $table->string('tipo_personeria', 20)->default('INDIVIDUAL');

            $table->boolean('is_active')->default(true);

            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fel_configs');
    }
};