<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matter_counterparties', function (Blueprint $table) {
            // [PROVISIONAL-FOUNDER-DECIDED] counterparty_role values
            $table->string('counterparty_role', 100)->nullable()->after('representing');
            $table->string('our_position')->default('we_represent')->after('counterparty_role');
            $table->text('notes')->nullable()->after('our_position');
        });
    }

    public function down(): void
    {
        Schema::table('matter_counterparties', function (Blueprint $table) {
            $table->dropColumn(['counterparty_role', 'our_position', 'notes']);
        });
    }
};
