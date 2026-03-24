<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_payables', function (Blueprint $table) {
            $table->id();

            $table->foreignId('supplier_id')
                ->constrained('suppliers')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->date('document_date');
            $table->string('document_no', 60)->nullable();
            $table->string('category', 50)->default('general'); // fuel, services, maintenance, general
            $table->string('description', 255);

            $table->decimal('original_amount_q', 12, 2);
            $table->decimal('paid_amount_q', 12, 2)->default(0);
            $table->decimal('balance_q', 12, 2);

            $table->string('status', 20)->default('pending'); // pending / paid / cancelled
            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->timestamps();

            $table->index(['supplier_id', 'status']);
            $table->index(['document_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_payables');
    }
};
