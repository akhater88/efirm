<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('automations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('name_ar');
            $table->string('name_en');
            $table->text('description')->nullable();
            $table->string('trigger_event', 100);
            $table->json('conditions');
            $table->boolean('is_active')->default(true);
            $table->integer('run_count')->default(0);
            $table->timestamp('last_run_at')->nullable();
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces');
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('automation_actions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('automation_id', 26);
            $table->integer('sort_order')->default(0);
            $table->string('action_type', 100);
            $table->json('action_payload');
            $table->boolean('stop_on_error')->default(true);

            $table->foreign('automation_id')->references('id')->on('automations')->cascadeOnDelete();
        });

        Schema::create('automation_runs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('automation_id', 26);
            $table->json('trigger_payload');
            $table->json('conditions_evaluation')->nullable();
            $table->json('actions_executed')->nullable();
            $table->string('status');
            $table->text('error_message')->nullable();
            $table->integer('duration_ms')->default(0);
            $table->timestamp('created_at');
            // NO updated_at, NO deleted_at — APPEND-ONLY

            $table->foreign('workspace_id')->references('id')->on('workspaces');
            $table->foreign('automation_id')->references('id')->on('automations');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('automation_runs');
        Schema::dropIfExists('automation_actions');
        Schema::dropIfExists('automations');
    }
};
