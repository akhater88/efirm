<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('task_workflow_id', 26)->nullable()->after('tags');
            $table->string('current_stage_id', 26)->nullable()->after('task_workflow_id');

            $table->foreign('task_workflow_id')->references('id')->on('task_workflows')->onDelete('set null');
            $table->foreign('current_stage_id')->references('id')->on('task_workflow_stages')->onDelete('set null');

            $table->index(['task_workflow_id', 'current_stage_id'], 'tasks_wf_stage_idx');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex('tasks_wf_stage_idx');
            $table->dropForeign(['current_stage_id']);
            $table->dropForeign(['task_workflow_id']);
            $table->dropColumn(['task_workflow_id', 'current_stage_id']);
        });
    }
};
