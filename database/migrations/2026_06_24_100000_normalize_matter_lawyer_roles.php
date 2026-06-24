<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        // Normalize legacy role values to match MatterLawyerRole enum
        // 'associate' → 'supporting' (the enum only supports 'lead' and 'supporting')
        DB::table('matter_lawyers')
            ->whereNotIn('role', ['lead', 'supporting'])
            ->update(['role' => 'supporting']);
    }

    public function down(): void
    {
        // No rollback — normalization is one-way
    }
};
