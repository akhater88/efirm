<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('library_clauses', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('title');
            $table->string('clause_type', 100)->nullable(); // [PROVISIONAL-FOUNDER-DECIDED]
            $table->string('practice_area')->nullable();
            $table->string('language')->default('bilingual');
            $table->json('body_ar')->nullable();
            $table->json('body_en')->nullable();
            $table->string('risk_position')->nullable(); // favourable, balanced, adverse
            $table->string('is_fallback_of_id', 26)->nullable();
            $table->json('tags')->nullable();
            $table->string('source_document_id', 26)->nullable();
            $table->unsignedInteger('usage_count')->default(0);
            $table->timestamp('last_used_at')->nullable();
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('is_fallback_of_id')->references('id')->on('library_clauses')->onDelete('set null');
            $table->foreign('source_document_id')->references('id')->on('documents')->onDelete('set null');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by_user_id')->references('id')->on('users')->onDelete('set null');

            $table->index('workspace_id');
            $table->index(['workspace_id', 'clause_type', 'practice_area'], 'lib_clauses_ws_type_area_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('library_clauses');
    }
};
