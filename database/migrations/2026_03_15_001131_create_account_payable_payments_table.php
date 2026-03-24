<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_payable_payments', function (Blueprint $table) {
            $table->id();

            $table->foreignId('account_payable_id')
                ->constrained('account_payables')
                ->cascadeOnUpdate()
                ->restrictOnDelete();

            $table->timestamp('paid_at');
            $table->decimal('amount_q', 12, 2);
            $table->string('payment_method', 20)->default('transfer');
            $table->text('notes')->nullable();

            $table->foreignId('created_by')
                ->nullable()
                ->constrained('users')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->timestamps();

            $table->index(['paid_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_payable_payments');
    }
};