<?php

/**
 * F-FIX-02.2 — Court Review Trainee Dispatch: add dispatch fields.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md Conversation 3.5, Decision #29.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('court_reviews', function (Blueprint $table) {
            $table->string('dispatched_to_user_id', 26)->nullable()->after('updated_by_user_id');
            $table->timestamp('dispatched_at')->nullable()->after('dispatched_to_user_id');
            $table->string('completed_by_user_id', 26)->nullable()->after('dispatched_at');
            $table->string('location_in_courthouse_ar', 200)->nullable()->after('completed_by_user_id');
            $table->string('location_in_courthouse_en', 200)->nullable()->after('location_in_courthouse_ar');
            $table->text('expected_outcome_ar')->nullable()->after('location_in_courthouse_en');
            $table->text('expected_outcome_en')->nullable()->after('expected_outcome_ar');
            $table->text('completion_notes')->nullable()->after('expected_outcome_en');
            $table->string('evidence_document_id', 26)->nullable()->after('completion_notes');

            $table->foreign('dispatched_to_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('completed_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('evidence_document_id')->references('id')->on('documents')->nullOnDelete();

            $table->index(['workspace_id', 'dispatched_to_user_id'], 'cr_workspace_dispatched_idx');
        });
    }

    public function down(): void
    {
        Schema::table('court_reviews', function (Blueprint $table) {
            $table->dropForeign(['dispatched_to_user_id']);
            $table->dropForeign(['completed_by_user_id']);
            $table->dropForeign(['evidence_document_id']);
            $table->dropIndex('cr_workspace_dispatched_idx');

            $table->dropColumn([
                'dispatched_to_user_id',
                'dispatched_at',
                'completed_by_user_id',
                'location_in_courthouse_ar',
                'location_in_courthouse_en',
                'expected_outcome_ar',
                'expected_outcome_en',
                'completion_notes',
                'evidence_document_id',
            ]);
        });
    }
};
