<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_generation_templates', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26)->nullable(); // null = system template
            $table->string('key', 100);
            $table->string('name_ar', 200);
            $table->string('name_en', 200);
            $table->text('description_ar')->nullable();
            $table->text('description_en')->nullable();
            $table->text('prompt_template');
            $table->json('intent_schema')->nullable();
            $table->unsignedInteger('version')->default(1);
            $table->string('legal_review_status')->default('pending'); // pending, approved, revoked
            $table->string('legal_review_approver_name', 200)->nullable();
            $table->date('legal_review_approver_date')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by_user_id')->references('id')->on('users')->onDelete('set null');

            $table->index('workspace_id');
            $table->index(['key', 'workspace_id'], 'ai_gen_templates_key_ws_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_generation_templates');
    }
};
