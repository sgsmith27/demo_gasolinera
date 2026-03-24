<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('fuel_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('fuel_id')->constrained('fuels')->cascadeOnUpdate()->restrictOnDelete();
            $table->decimal('price_per_liter', 10, 4);
            $table->timestamp('valid_from'); // desde cuándo aplica
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->timestamps();

            $table->index(['fuel_id', 'valid_from']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('fuel_prices');
    }
};