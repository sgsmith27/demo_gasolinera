<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('nozzles', function (Blueprint $table) {
            $table->id();
            $table->foreignId('pump_id')->constrained('pumps')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('fuel_id')->constrained('fuels')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('code', 30)->unique(); // ej: B1-REG, B1-SUP
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['pump_id', 'fuel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('nozzles');
    }
};