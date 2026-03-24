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
    Schema::table('expenses', function (Blueprint $table) {

        $table->string('status',20)->default('active')->after('notes');

        $table->timestamp('voided_at')->nullable()->after('status');

        $table->foreignId('voided_by')
            ->nullable()
            ->constrained('users')
            ->nullOnDelete()
            ->cascadeOnUpdate();

        $table->text('void_reason')->nullable()->after('voided_by');

    });
}

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('expenses', function (Blueprint $table) {
            //
        });
    }
};
