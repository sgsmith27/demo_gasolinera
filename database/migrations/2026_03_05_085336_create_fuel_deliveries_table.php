<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fuel_deliveries', function (Blueprint $table) {
            $table->id();

            $table->timestamp('delivered_at');

            $table->foreignId('tank_id')->constrained('tanks')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('fuel_id')->constrained('fuels')->cascadeOnUpdate()->restrictOnDelete();

            $table->decimal('liters', 14, 3);
            $table->decimal('total_cost_q', 14, 2)->nullable(); // fase 1 opcional

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('notes', 255)->nullable();

            $table->timestamps();

            $table->index(['delivered_at']);
            $table->index(['tank_id', 'delivered_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_deliveries');
    }
};