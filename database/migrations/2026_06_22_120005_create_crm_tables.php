<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pipelines', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->json('stages');
            $table->boolean('is_default')->default(false);
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->cascadeOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by_user_id')->references('id')->on('users')->nullOnDelete();

            $table->index('workspace_id');
        });

        Schema::create('leads', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('contact_id', 26)->nullable();
            $table->string('pipeline_id', 26)->nullable();
            $table->string('title', 255);
            $table->string('source', 30)->nullable();
            $table->string('status', 20)->default('new');
            $table->string('current_stage', 100)->nullable();
            $table->text('notes')->nullable();
            $table->string('assigned_to_user_id', 26)->nullable();
            $table->string('converted_to_opportunity_id', 26)->nullable();
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->cascadeOnDelete();
            $table->foreign('contact_id')->references('id')->on('contacts')->nullOnDelete();
            $table->foreign('pipeline_id')->references('id')->on('pipelines')->nullOnDelete();
            $table->foreign('assigned_to_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by_user_id')->references('id')->on('users')->nullOnDelete();

            $table->index(['workspace_id', 'status']);
            $table->index(['workspace_id', 'pipeline_id']);
        });

        Schema::create('opportunities', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('contact_id', 26);
            $table->string('pipeline_id', 26)->nullable();
            $table->string('lead_id', 26)->nullable();
            $table->string('title', 255);
            $table->string('status', 20)->default('open');
            $table->string('current_stage', 100)->nullable();
            $table->decimal('estimated_value', 15, 2)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->date('expected_close_date')->nullable();
            $table->string('converted_to_matter_id', 26)->nullable();
            $table->text('notes')->nullable();
            $table->string('assigned_to_user_id', 26)->nullable();
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->cascadeOnDelete();
            $table->foreign('contact_id')->references('id')->on('contacts')->cascadeOnDelete();
            $table->foreign('pipeline_id')->references('id')->on('pipelines')->nullOnDelete();
            $table->foreign('lead_id')->references('id')->on('leads')->nullOnDelete();
            $table->foreign('converted_to_matter_id')->references('id')->on('matters')->nullOnDelete();
            $table->foreign('assigned_to_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by_user_id')->references('id')->on('users')->nullOnDelete();

            $table->index(['workspace_id', 'status']);
            $table->index(['workspace_id', 'pipeline_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('opportunities');
        Schema::dropIfExists('leads');
        Schema::dropIfExists('pipelines');
    }
};
