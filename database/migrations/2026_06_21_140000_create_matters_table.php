<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matters', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('title');
            $table->string('client_id', 26);
            $table->string('practice_area')->default('commercial_contracts');
            $table->string('status')->default('active');
            $table->string('stage', 100)->nullable();
            $table->text('description')->nullable();
            $table->string('internal_reference', 100)->nullable();
            $table->string('lead_lawyer_id', 26)->nullable();
            $table->date('opened_at')->nullable();
            $table->date('closed_at')->nullable();
            $table->json('tags')->nullable();
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('client_id')->references('id')->on('contacts')->onDelete('restrict');
            $table->foreign('lead_lawyer_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by_user_id')->references('id')->on('users')->onDelete('set null');

            $table->index('workspace_id');
            $table->index(['workspace_id', 'status'], 'matters_workspace_status_idx');
            $table->index(['workspace_id', 'practice_area'], 'matters_workspace_practice_area_idx');
            $table->index('client_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matters');
    }
};
