<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('documents', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('matter_id', 26);
            $table->string('title');
            $table->string('document_type')->default('contract'); // [PROVISIONAL-FOUNDER-DECIDED]
            $table->string('language_primary')->default('bilingual');
            $table->string('status')->default('draft');
            $table->string('current_version_id', 26)->nullable();
            $table->string('original_file_url', 500)->nullable();
            $table->json('metadata')->nullable();
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('matter_id')->references('id')->on('matters')->onDelete('restrict');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by_user_id')->references('id')->on('users')->onDelete('set null');

            $table->index('workspace_id');
            $table->index(['workspace_id', 'status'], 'documents_workspace_status_idx');
            $table->index('matter_id');
            $table->index('current_version_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('documents');
    }
};
