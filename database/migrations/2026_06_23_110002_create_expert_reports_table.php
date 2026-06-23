<?php

/**
 * F-FIX-01.2 — Expert Report entity.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md
 * Conversation 1, Decisions #3 (expert report entity) and #19 (8-day countdown from day after receipt).
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('expert_reports', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('matter_id', 26);
            $table->string('expert_name_ar', 200);
            $table->string('expert_name_en', 200)->nullable();
            $table->string('report_type'); // ExpertReportType enum
            $table->date('received_date');
            $table->date('objection_deadline_date'); // auto-computed: received_date + 8 days
            $table->boolean('objection_filed')->default(false);
            $table->date('objection_filed_date')->nullable();
            $table->string('our_position')->default('not_yet_reviewed'); // ExpertReportPosition enum
            $table->text('summary_ar')->nullable();
            $table->text('summary_en')->nullable();
            $table->string('document_id', 26)->nullable();

            // Audit columns
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('workspace_id')->references('id')->on('workspaces');
            $table->foreign('matter_id')->references('id')->on('matters');
            $table->foreign('document_id')->references('id')->on('documents');
            $table->foreign('created_by_user_id')->references('id')->on('users');
            $table->foreign('updated_by_user_id')->references('id')->on('users');

            // Index for deadline queries
            $table->index(['workspace_id', 'objection_deadline_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('expert_reports');
    }
};
