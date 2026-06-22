<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('trust_accounts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('contact_id', 26);
            $table->string('matter_id', 26)->nullable();
            $table->string('name', 255);
            $table->string('bank_name', 255)->nullable();
            $table->string('bank_account_number', 100)->nullable();
            $table->string('currency', 3)->default('USD');
            $table->decimal('balance', 15, 2)->default(0);
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->cascadeOnDelete();
            $table->foreign('contact_id')->references('id')->on('contacts')->cascadeOnDelete();
            $table->foreign('matter_id')->references('id')->on('matters')->nullOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by_user_id')->references('id')->on('users')->nullOnDelete();

            $table->index(['workspace_id', 'contact_id']);
        });

        Schema::create('trust_ledger_entries', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('trust_account_id', 26);
            $table->string('type', 20);
            $table->decimal('amount', 15, 2);
            $table->decimal('balance_after', 15, 2);
            $table->text('description')->nullable();
            $table->string('reference', 255)->nullable();
            $table->string('created_by_user_id', 26)->nullable();
            $table->timestamp('created_at')->nullable();
            // APPEND-ONLY: no updated_at, no deleted_at

            $table->foreign('workspace_id')->references('id')->on('workspaces')->cascadeOnDelete();
            $table->foreign('trust_account_id')->references('id')->on('trust_accounts')->cascadeOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();

            $table->index(['workspace_id', 'trust_account_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('trust_ledger_entries');
        Schema::dropIfExists('trust_accounts');
    }
};
