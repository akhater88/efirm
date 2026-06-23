<?php

/**
 * F-FIX-02.1 — Hearing Session History: hearing_action_items table.
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
        Schema::create('hearing_action_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('hearing_id', 26);
            $table->text('description_ar');
            $table->text('description_en')->nullable();
            $table->date('due_date');
            $table->string('responsible_user_id', 26)->nullable();
            $table->string('status', 30)->default('pending');
            $table->string('obligation_id', 26)->nullable();
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('hearing_id')->references('id')->on('hearings')->onDelete('cascade');
            $table->foreign('responsible_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('obligation_id')->references('id')->on('obligations')->nullOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by_user_id')->references('id')->on('users')->nullOnDelete();

            $table->index(['workspace_id', 'due_date'], 'hai_workspace_due_date_idx');
            $table->index(['responsible_user_id', 'status'], 'hai_responsible_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hearing_action_items');
    }
};
