<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('fel_documents', function (Blueprint $table) {
            $table->decimal('idp_amount_q', 12, 2)->default(0)->after('total_amount_q');
            $table->decimal('vat_amount_q', 12, 2)->default(0)->after('idp_amount_q');
            $table->decimal('taxable_base_q', 12, 2)->default(0)->after('vat_amount_q');
        });
    }

    public function down(): void
    {
        Schema::table('fel_documents', function (Blueprint $table) {
            $table->dropColumn([
                'idp_amount_q',
                'vat_amount_q',
                'taxable_base_q',
            ]);
        });
    }
};
