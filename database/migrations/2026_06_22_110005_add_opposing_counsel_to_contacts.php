<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('contacts', function (Blueprint $table) {
            $table->boolean('is_opposing_counsel')->default(false)->after('is_counterparty');
        });

        Schema::table('matter_counterparties', function (Blueprint $table) {
            $table->string('opposing_counsel_contact_id', 26)->nullable()->after('notes');

            $table->foreign('opposing_counsel_contact_id')
                ->references('id')
                ->on('contacts')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('matter_counterparties', function (Blueprint $table) {
            $table->dropForeign(['opposing_counsel_contact_id']);
            $table->dropColumn('opposing_counsel_contact_id');
        });

        Schema::table('contacts', function (Blueprint $table) {
            $table->dropColumn('is_opposing_counsel');
        });
    }
};
