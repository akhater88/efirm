<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_clauses', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('document_version_id', 26);
            $table->unsignedInteger('position');
            $table->string('clause_path');
            $table->string('title')->nullable();
            $table->json('body');
            $table->string('language')->default('mixed');
            $table->string('clause_type', 100)->nullable();
            $table->timestamps();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('document_version_id')->references('id')->on('document_versions')->onDelete('cascade');

            $table->index(['document_version_id', 'position'], 'doc_clauses_version_position_idx');
            $table->index('workspace_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_clauses');
    }
};
