<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('tanks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fuel_id')->constrained('fuels')->cascadeOnUpdate()->restrictOnDelete();
            $table->string('name', 100)->nullable(); // Tanque Regular, etc.
            $table->decimal('capacity_liters', 14, 3)->nullable(); // capacidad (L)
            $table->decimal('current_liters', 14, 3)->default(0);  // existencias (L)
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['fuel_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tanks');
    }
};