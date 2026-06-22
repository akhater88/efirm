<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_document_generations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('matter_id', 26);
            $table->string('user_id', 26);
            $table->string('template_key', 100);
            $table->json('intent_payload');
            $table->text('prompt_used');
            $table->string('model_used', 100)->nullable();
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->decimal('cost_usd', 10, 6)->default(0);
            $table->unsignedInteger('latency_ms')->default(0);
            $table->string('generated_document_id', 26)->nullable();
            $table->string('status')->default('complete'); // queued, generating, complete, failed
            $table->text('error_message')->nullable();
            $table->string('created_by_user_id', 26)->nullable();
            $table->timestamp('created_at')->nullable();
            // APPEND-ONLY: no updated_at, no deleted_at

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('matter_id')->references('id')->on('matters');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('generated_document_id')->references('id')->on('documents')->onDelete('set null');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');

            $table->index('workspace_id');
            $table->index(['workspace_id', 'matter_id'], 'ai_doc_gen_ws_matter_idx');
            $table->index(['workspace_id', 'template_key'], 'ai_doc_gen_ws_template_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_document_generations');
    }
};
