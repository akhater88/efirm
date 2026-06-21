<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ai_interactions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('user_id', 26);
            $table->string('document_id', 26)->nullable();
            $table->string('document_clause_id', 26)->nullable();
            $table->string('interaction_type'); // draft, review, suggest, translate, explain
            $table->text('prompt');
            $table->text('response')->nullable();
            $table->string('model', 100);
            $table->unsignedInteger('input_tokens')->default(0);
            $table->unsignedInteger('output_tokens')->default(0);
            $table->decimal('cost_usd', 10, 6)->default(0);
            $table->unsignedInteger('latency_ms')->default(0);
            $table->boolean('was_accepted')->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('set null');

            $table->index('workspace_id');
            $table->index(['workspace_id', 'user_id'], 'ai_interactions_ws_user_idx');
            $table->index(['workspace_id', 'interaction_type'], 'ai_interactions_ws_type_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ai_interactions');
    }
};
