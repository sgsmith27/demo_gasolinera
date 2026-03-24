<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fuel_deliveries', function (Blueprint $table) {
            $table->string('status', 20)->default('active')->after('notes');
            $table->timestamp('voided_at')->nullable()->after('status');
            $table->foreignId('voided_by')->nullable()->after('voided_at')->constrained('users')->nullOnDelete()->cascadeOnUpdate();
            $table->text('void_reason')->nullable()->after('voided_by');

            $table->index(['status']);
        });
    }

    public function down(): void
    {
        Schema::table('fuel_deliveries', function (Blueprint $table) {
            $table->dropConstrainedForeignId('voided_by');
            $table->dropColumn(['status', 'voided_at', 'void_reason']);
        });
    }
};