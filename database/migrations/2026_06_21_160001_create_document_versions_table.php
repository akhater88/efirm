<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_versions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('document_id', 26);
            $table->unsignedInteger('version_number');
            $table->json('body');
            $table->char('body_hash', 64);
            $table->text('change_summary')->nullable();
            $table->string('created_by_user_id', 26)->nullable();
            $table->timestamp('created_at')->nullable();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');

            $table->unique(['document_id', 'version_number'], 'doc_versions_doc_version_unique');
            $table->index('workspace_id');
            $table->index('document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_versions');
    }
};
