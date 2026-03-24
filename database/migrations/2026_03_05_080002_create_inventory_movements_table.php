<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('inventory_movements', function (Blueprint $table) {
            $table->id();

            $table->timestamp('moved_at');

            $table->foreignId('tank_id')->constrained('tanks')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('fuel_id')->constrained('fuels')->cascadeOnUpdate()->restrictOnDelete();

            // IN / OUT / ADJUST
            $table->string('movement_type', 10);

            // positivo o negativo según el tipo, pero aquí lo guardamos con signo:
            // IN: +, OUT: -, ADJUST: +/-.
            $table->decimal('liters_delta', 14, 3);

            // referencia (por ahora SALE o NONE)
            $table->string('reference_type', 10)->default('NONE'); // SALE/PIPA/ADJUST/NONE
            $table->unsignedBigInteger('reference_id')->nullable();

            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->string('notes', 255)->nullable();

            $table->timestamps();

            $table->index(['tank_id', 'moved_at']);
            $table->index(['fuel_id', 'moved_at']);
            $table->index(['reference_type', 'reference_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inventory_movements');
    }
};