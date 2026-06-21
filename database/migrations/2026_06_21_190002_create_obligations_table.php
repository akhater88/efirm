<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('obligations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('document_id', 26);
            $table->string('clause_id', 26)->nullable();
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('obligation_type')->default('other'); // [PROVISIONAL-FOUNDER-DECIDED]
            $table->string('responsible_party')->default('us');
            $table->string('responsible_user_id', 26)->nullable();
            $table->date('due_date');
            $table->unsignedInteger('reminder_days_before')->default(7);
            $table->string('status')->default('pending');
            $table->timestamp('completed_at')->nullable();
            $table->string('completed_by_id', 26)->nullable();
            $table->decimal('monetary_amount', 15, 2)->nullable();
            $table->char('monetary_currency', 3)->nullable();
            $table->text('notes')->nullable();
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('responsible_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('completed_by_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by_user_id')->references('id')->on('users')->onDelete('set null');

            $table->index('workspace_id');
            $table->index(['workspace_id', 'due_date'], 'obligations_ws_due_date_idx');
            $table->index(['workspace_id', 'status'], 'obligations_ws_status_idx');
            $table->index('document_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('obligations');
    }
};
