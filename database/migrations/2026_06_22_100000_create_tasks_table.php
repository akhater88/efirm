<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('tasks', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('taskable_type', 100);
            $table->string('taskable_id', 26);
            $table->string('assigned_to_user_id', 26)->nullable();
            $table->date('due_date')->nullable();
            $table->string('priority')->default('normal');
            $table->string('status')->default('todo');
            $table->timestamp('completed_at')->nullable();
            $table->string('completed_by_user_id', 26)->nullable();
            $table->json('tags')->nullable();
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('assigned_to_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('completed_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by_user_id')->references('id')->on('users')->onDelete('set null');

            $table->index('workspace_id');
            $table->index(['workspace_id', 'status', 'due_date'], 'tasks_ws_status_due_idx');
            $table->index(['taskable_type', 'taskable_id'], 'tasks_taskable_idx');
            $table->index('assigned_to_user_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('tasks');
    }
};
