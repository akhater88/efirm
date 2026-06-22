<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('court_reviews', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('matter_id', 26);
            $table->string('hearing_id', 26)->nullable();
            $table->date('decision_date');
            $table->string('decision_type');
            $table->string('outcome');
            $table->text('summary_ar')->nullable();
            $table->text('summary_en')->nullable();
            $table->string('decision_document_id', 26)->nullable();
            $table->boolean('appealable')->default(false);
            $table->date('appeal_deadline_date')->nullable();
            $table->boolean('appeal_filed')->default(false);
            $table->text('next_steps')->nullable();
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('matter_id')->references('id')->on('matters')->onDelete('cascade');
            $table->foreign('hearing_id')->references('id')->on('hearings')->nullOnDelete();
            $table->foreign('decision_document_id')->references('id')->on('documents')->nullOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('court_reviews');
    }
};
