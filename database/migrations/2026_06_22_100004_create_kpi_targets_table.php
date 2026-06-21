<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('kpi_targets', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('targetable_type', 100); // user or team
            $table->string('targetable_id', 26);
            $table->string('metric'); // billable_hours_monthly, matters_opened_monthly, etc.
            $table->decimal('target_value', 12, 2);
            $table->string('period'); // monthly, quarterly, annual
            $table->date('effective_from');
            $table->date('effective_to')->nullable();
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by_user_id')->references('id')->on('users')->onDelete('set null');

            $table->index('workspace_id');
            $table->index(['targetable_type', 'targetable_id'], 'kpi_targets_targetable_idx');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('kpi_targets');
    }
};
