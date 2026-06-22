<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matters', function (Blueprint $table) {
            $table->boolean('is_litigation')->default(false)->after('tags');
            $table->string('court_id', 26)->nullable()->after('is_litigation');
            $table->string('judge_id', 26)->nullable()->after('court_id');
            $table->string('court_case_number', 100)->nullable()->after('judge_id');
            $table->string('case_number_internal', 100)->nullable()->after('court_case_number');
            $table->string('litigation_status')->nullable()->after('case_number_internal');
            $table->date('filed_date')->nullable()->after('litigation_status');
            $table->date('next_hearing_date')->nullable()->after('filed_date');
            $table->string('representation_role')->nullable()->after('next_hearing_date');

            $table->foreign('court_id')->references('id')->on('courts')->nullOnDelete();
            $table->foreign('judge_id')->references('id')->on('judges')->nullOnDelete();

            $table->index(['workspace_id', 'is_litigation', 'litigation_status'], 'matters_litigation_idx');
        });
    }

    public function down(): void
    {
        Schema::table('matters', function (Blueprint $table) {
            $table->dropIndex('matters_litigation_idx');
            $table->dropForeign(['court_id']);
            $table->dropForeign(['judge_id']);
            $table->dropColumn([
                'is_litigation',
                'court_id',
                'judge_id',
                'court_case_number',
                'case_number_internal',
                'litigation_status',
                'filed_date',
                'next_hearing_date',
                'representation_role',
            ]);
        });
    }
};
