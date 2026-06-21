<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('time_entries', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('user_id', 26);
            $table->string('matter_id', 26)->nullable();
            $table->string('document_id', 26)->nullable();
            $table->string('task_id', 26)->nullable();
            $table->text('description');
            $table->unsignedInteger('duration_minutes');
            $table->dateTime('started_at');
            $table->dateTime('ended_at');
            $table->boolean('is_billable')->default(true);
            $table->decimal('billing_rate_per_hour', 8, 2)->nullable();
            $table->char('currency', 3)->nullable();
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('matter_id')->references('id')->on('matters')->onDelete('set null');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('set null');
            $table->foreign('task_id')->references('id')->on('tasks')->onDelete('set null');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by_user_id')->references('id')->on('users')->onDelete('set null');

            $table->index('workspace_id');
            $table->index(['workspace_id', 'user_id', 'started_at'], 'time_entries_ws_user_started_idx');
            $table->index('matter_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('time_entries');
    }
};
