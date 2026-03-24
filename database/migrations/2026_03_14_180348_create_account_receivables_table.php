<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('account_receivables', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained('customers')->cascadeOnUpdate()->restrictOnDelete();
            $table->foreignId('sale_id')->constrained('sales')->cascadeOnUpdate()->restrictOnDelete();

            $table->date('document_date');
            $table->decimal('original_amount_q', 12, 2);
            $table->decimal('paid_amount_q', 12, 2)->default(0);
            $table->decimal('balance_q', 12, 2);

            $table->string('status', 20)->default('pending'); // pending / paid / cancelled
            $table->text('notes')->nullable();

            $table->timestamps();

            $table->index(['customer_id', 'status']);
            $table->index(['document_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('account_receivables');
    }
};


