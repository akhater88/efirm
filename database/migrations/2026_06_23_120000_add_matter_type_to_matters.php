<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matters', function (Blueprint $table) {
            $table->string('matter_type', 50)->nullable()->after('is_litigation');
            $table->date('target_closing_date')->nullable()->after('matter_type');
            $table->char('deal_value_currency', 3)->nullable()->after('target_closing_date');
            $table->decimal('deal_value_amount', 15, 2)->nullable()->after('deal_value_currency');
            $table->json('expected_document_types')->nullable()->after('deal_value_amount');

            $table->index(['workspace_id', 'matter_type'], 'matters_workspace_matter_type_idx');
        });
    }

    public function down(): void
    {
        Schema::table('matters', function (Blueprint $table) {
            $table->dropIndex('matters_workspace_matter_type_idx');
            $table->dropColumn([
                'matter_type',
                'target_closing_date',
                'deal_value_currency',
                'deal_value_amount',
                'expected_document_types',
            ]);
        });
    }
};
