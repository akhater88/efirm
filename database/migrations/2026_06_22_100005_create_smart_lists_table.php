<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smart_lists', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('user_id', 26);
            $table->string('entity_type', 100); // morph map key: matter, contact, task, etc.
            $table->string('name', 100);
            $table->json('filters');
            $table->json('sort_order')->nullable();
            $table->boolean('is_shared_to_workspace')->default(false);
            $table->boolean('is_pinned')->default(false);
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by_user_id')->references('id')->on('users')->onDelete('set null');

            $table->index('workspace_id');
            $table->index(['workspace_id', 'user_id', 'entity_type'], 'smart_lists_ws_user_entity_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smart_lists');
    }
};
