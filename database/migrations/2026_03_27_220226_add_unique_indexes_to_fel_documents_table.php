<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("
            CREATE UNIQUE INDEX IF NOT EXISTS fel_documents_one_active_fact_per_sale_idx
            ON fel_documents (sale_id, doc_type)
            WHERE doc_type = 'FACT'
              AND fel_status IN ('pending', 'certified')
        ");

        DB::statement("
            CREATE UNIQUE INDEX IF NOT EXISTS fel_documents_uuid_unique_not_null_idx
            ON fel_documents (uuid)
            WHERE uuid IS NOT NULL
        ");
    }

    public function down(): void
    {
        DB::statement('DROP INDEX IF EXISTS fel_documents_one_active_fact_per_sale_idx');
        DB::statement('DROP INDEX IF EXISTS fel_documents_uuid_unique_not_null_idx');
    }
};