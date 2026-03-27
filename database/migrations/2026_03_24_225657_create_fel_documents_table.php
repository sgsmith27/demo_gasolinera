<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('fel_documents', function (Blueprint $table) {
        $table->id();

        $table->foreignId('sale_id')
            ->nullable()
            ->constrained('sales')
            ->nullOnDelete();

        $table->foreignId('customer_id')
            ->nullable()
            ->constrained('customers')
            ->nullOnDelete();

        $table->string('doc_type', 10)->default('FACT'); // FACT, NCRE, NDEB
        $table->string('environment', 10)->default('test'); // test | production

        $table->string('fel_status', 20)->default('pending'); 
        // pending | certified | error | cancelled

        $table->string('uuid')->nullable();
        $table->string('series', 20)->nullable();
        $table->string('number', 50)->nullable();

        $table->timestamp('issued_at')->nullable();

        $table->string('receiver_taxid', 20)->nullable();
        $table->string('receiver_name')->nullable();

        $table->decimal('total_amount_q', 12, 2)->default(0);

        $table->json('request_payload')->nullable();
        $table->json('response_payload')->nullable();

        $table->longText('xml')->nullable();
        $table->longText('pdf')->nullable();
        $table->longText('html')->nullable();

        $table->text('error_message')->nullable();

        $table->foreignId('created_by')
            ->nullable()
            ->constrained('users')
            ->nullOnDelete();

        $table->timestamps();

        $table->index(['sale_id']);
        $table->index(['fel_status']);
        $table->index(['uuid']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fel_documents');
    }
};
