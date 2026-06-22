<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('parent_id', 26)->nullable();
            $table->string('code', 20);
            $table->string('name', 255);
            $table->string('name_ar', 255)->nullable();
            $table->string('account_type', 20);
            $table->boolean('is_system')->default(false);
            $table->text('description')->nullable();
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->cascadeOnDelete();
            $table->foreign('parent_id')->references('id')->on('accounts')->nullOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by_user_id')->references('id')->on('users')->nullOnDelete();

            $table->unique(['workspace_id', 'code']);
            $table->index(['workspace_id', 'account_type']);
            $table->index(['workspace_id', 'parent_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};
