<?php

/**
 * F-FIX-02.1 — Hearing Session History: add session content fields.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md Conversation 3.5, Decision #28.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hearings', function (Blueprint $table) {
            $table->text('judge_statement_ar')->nullable()->after('outcome');
            $table->text('judge_statement_en')->nullable()->after('judge_statement_ar');
            $table->text('outcome_summary_ar')->nullable()->after('judge_statement_en');
            $table->text('outcome_summary_en')->nullable()->after('outcome_summary_ar');
            $table->text('our_submissions_made')->nullable()->after('outcome_summary_en');
            $table->text('opposing_submissions_made')->nullable()->after('our_submissions_made');
            $table->text('next_session_required_actions_ar')->nullable()->after('opposing_submissions_made');
            $table->text('next_session_required_actions_en')->nullable()->after('next_session_required_actions_ar');
            $table->json('session_attended_by')->nullable()->after('next_session_required_actions_en');
        });
    }

    public function down(): void
    {
        Schema::table('hearings', function (Blueprint $table) {
            $table->dropColumn([
                'judge_statement_ar',
                'judge_statement_en',
                'outcome_summary_ar',
                'outcome_summary_en',
                'our_submissions_made',
                'opposing_submissions_made',
                'next_session_required_actions_ar',
                'next_session_required_actions_en',
                'session_attended_by',
            ]);
        });
    }
};
