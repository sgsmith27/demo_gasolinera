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
        Schema::create('fel_events', function (Blueprint $table) {
        $table->id();

        $table->foreignId('fel_document_id')
            ->constrained('fel_documents')
            ->cascadeOnDelete();

        $table->string('event_type', 30);
        // create | send | success | error | cancel | retry

        $table->text('description')->nullable();

        $table->json('payload')->nullable();
        $table->json('response')->nullable();

        $table->timestamp('created_at')->useCurrent();

        $table->index(['fel_document_id']);
        $table->index(['event_type']);
    });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('fel_events');
    }
};
