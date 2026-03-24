<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('sales', function (Blueprint $table) {
            $table->id();

            $table->timestamp('sold_at');

            // despachador (usuario)
            $table->foreignId('user_id')->constrained('users')->cascadeOnUpdate()->restrictOnDelete();

            // manguera/bomba y combustible
            $table->foreignId('nozzle_id')->constrained('nozzles')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('fuel_id')->constrained('fuels')->cascadeOnUpdate()->restrictOnDelete();

            // precio aplicado y cantidades
            $table->decimal('price_per_liter', 10, 4);
            $table->decimal('liters', 14, 3);
            $table->decimal('total_amount_q', 14, 2);

            $table->string('sale_mode', 10); // 'amount' o 'volume'
            $table->string('payment_method', 20)->default('cash'); // cash/card/transfer (fase 1 simple)
            $table->string('notes', 255)->nullable();

            $table->timestamps();

            $table->index(['sold_at']);
            $table->index(['user_id', 'sold_at']);
            $table->index(['nozzle_id', 'sold_at']);
            $table->index(['fuel_id', 'sold_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sales');
    }
};