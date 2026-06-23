<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hearings', function (Blueprint $table) {
            $table->text('postponement_reason_ar')->nullable()->after('postponed_to_hearing_id');
            $table->text('postponement_reason_en')->nullable()->after('postponement_reason_ar');
            $table->string('postponement_initiated_by', 50)->nullable()->after('postponement_reason_en');
        });
    }

    public function down(): void
    {
        Schema::table('hearings', function (Blueprint $table) {
            $table->dropColumn([
                'postponement_reason_ar',
                'postponement_reason_en',
                'postponement_initiated_by',
            ]);
        });
    }
};
