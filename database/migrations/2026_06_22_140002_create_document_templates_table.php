<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('document_templates', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26)->nullable();
            $table->string('name_ar');
            $table->string('name_en');
            $table->text('description')->nullable();
            $table->string('document_type');
            $table->string('language')->default('bilingual');
            $table->json('body');
            $table->json('placeholder_schema')->nullable();
            $table->boolean('is_active')->default(true);
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->nullOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('document_templates');
    }
};
