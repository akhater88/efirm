<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('hearings', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('matter_id', 26);
            $table->dateTime('hearing_date');
            $table->string('court_id', 26);
            $table->string('judge_id', 26)->nullable();
            $table->string('hearing_type');
            $table->string('status');
            $table->dateTime('held_at')->nullable();
            $table->text('outcome')->nullable();
            $table->text('next_action_required')->nullable();
            $table->string('postponed_to_hearing_id', 26)->nullable();
            $table->string('our_attendee_user_id', 26)->nullable();
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('matter_id')->references('id')->on('matters')->onDelete('cascade');
            $table->foreign('court_id')->references('id')->on('courts')->onDelete('cascade');
            $table->foreign('judge_id')->references('id')->on('judges')->nullOnDelete();
            $table->foreign('postponed_to_hearing_id')->references('id')->on('hearings')->nullOnDelete();
            $table->foreign('our_attendee_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by_user_id')->references('id')->on('users')->nullOnDelete();

            $table->index(['workspace_id', 'hearing_date'], 'hearings_workspace_date_idx');
            $table->index(['matter_id', 'hearing_date'], 'hearings_matter_date_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('hearings');
    }
};
