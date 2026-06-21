<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kyc_checklists', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('contact_id', 26);
            $table->string('status')->default('not_started');
            $table->date('started_at')->nullable();
            $table->date('completed_at')->nullable();
            $table->date('next_review_date')->nullable();
            $table->text('notes')->nullable();
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by_user_id')->references('id')->on('users')->onDelete('set null');

            $table->index('workspace_id');
            $table->index('contact_id');
        });

        Schema::create('kyc_items', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('kyc_checklist_id', 26);
            $table->string('item_type', 100); // [ADVISOR-REVIEW-RECOMMENDED]
            $table->string('status')->default('not_requested');
            $table->date('expiry_date')->nullable();
            $table->string('document_id', 26)->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('kyc_checklist_id')->references('id')->on('kyc_checklists')->onDelete('cascade');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('set null');

            $table->index('kyc_checklist_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kyc_items');
        Schema::dropIfExists('kyc_checklists');
    }
};
