<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('work_shifts', function (Blueprint $table) {
            $table->string('assignment_mode', 20)->default('free')->after('status');
            $table->foreignId('pump_id')
                ->nullable()
                ->after('assignment_mode')
                ->constrained('pumps')
                ->nullOnDelete()
                ->cascadeOnUpdate();

            $table->index(['assignment_mode']);
        });
    }

    public function down(): void
    {
        Schema::table('work_shifts', function (Blueprint $table) {
            $table->dropConstrainedForeignId('pump_id');
            $table->dropColumn(['assignment_mode']);
        });
    }
};