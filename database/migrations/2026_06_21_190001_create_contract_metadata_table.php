<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contract_metadata', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('document_id', 26)->unique();
            $table->decimal('contract_value', 15, 2)->nullable();
            $table->char('contract_currency', 3)->nullable();
            $table->date('effective_date')->nullable();
            $table->unsignedInteger('term_months')->nullable();
            $table->date('expiry_date')->nullable();
            $table->boolean('auto_renew')->default(false);
            $table->unsignedInteger('renewal_notice_period_days')->nullable();
            $table->string('governing_law', 100)->nullable(); // [PROVISIONAL-FOUNDER-DECIDED]
            $table->string('jurisdiction_clause')->nullable();
            $table->date('signed_date')->nullable();
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('document_id')->references('id')->on('documents')->onDelete('cascade');
            $table->foreign('created_by_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by_user_id')->references('id')->on('users')->onDelete('set null');

            $table->index('workspace_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('contract_metadata');
    }
};
