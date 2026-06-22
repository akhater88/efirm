<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_workflows', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('name', 100);
            $table->text('description')->nullable();
            $table->boolean('is_default')->default(false);
            $table->string('applies_to_task_type', 100)->nullable();
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by_user_id')->references('id')->on('users')->onDelete('set null');

            $table->index('workspace_id');
        });

        Schema::create('task_workflow_stages', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('task_workflow_id', 26);
            $table->string('name_ar', 100);
            $table->string('name_en', 100);
            $table->string('key', 50);
            $table->integer('sort_order');
            $table->boolean('is_initial')->default(false);
            $table->boolean('is_terminal')->default(false);
            $table->string('color', 20)->default('gray');
            $table->boolean('requires_approval')->default(false);

            $table->foreign('task_workflow_id')->references('id')->on('task_workflows')->onDelete('cascade');
            $table->unique(['task_workflow_id', 'key']);
        });

        Schema::create('task_workflow_transitions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('task_workflow_id', 26);
            $table->string('from_stage_id', 26);
            $table->string('to_stage_id', 26);
            $table->string('requires_role')->nullable();
            $table->string('requires_approval_by_user_id', 26)->nullable();
            $table->integer('auto_transition_after_hours')->nullable();

            $table->foreign('task_workflow_id')->references('id')->on('task_workflows')->onDelete('cascade');
            $table->foreign('from_stage_id')->references('id')->on('task_workflow_stages')->onDelete('cascade');
            $table->foreign('to_stage_id')->references('id')->on('task_workflow_stages')->onDelete('cascade');
            $table->foreign('requires_approval_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->unique(['task_workflow_id', 'from_stage_id', 'to_stage_id'], 'twt_wf_from_to_unique');
        });

        Schema::create('task_workflow_approvals', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('task_id', 26);
            $table->string('from_stage_id', 26);
            $table->string('to_stage_id', 26);
            $table->string('requested_by_user_id', 26);
            $table->string('approver_user_id', 26);
            $table->string('status')->default('pending');
            $table->timestamp('responded_at')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('cascade');
            $table->foreign('from_stage_id')->references('id')->on('task_workflow_stages');
            $table->foreign('to_stage_id')->references('id')->on('task_workflow_stages');
            $table->foreign('requested_by_user_id')->references('id')->on('users');
            $table->foreign('approver_user_id')->references('id')->on('users');

            $table->index(['workspace_id', 'status'], 'twa_ws_status_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_workflow_approvals');
        Schema::dropIfExists('task_workflow_transitions');
        Schema::dropIfExists('task_workflow_stages');
        Schema::dropIfExists('task_workflows');
    }
};
