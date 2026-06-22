<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('invoice_id', 26);
            $table->string('receipt_number', 50);
            $table->decimal('amount', 15, 2);
            $table->string('payment_method', 30);
            $table->date('received_date');
            $table->string('reference', 255)->nullable();
            $table->text('notes')->nullable();
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->cascadeOnDelete();
            $table->foreign('invoice_id')->references('id')->on('invoices')->cascadeOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by_user_id')->references('id')->on('users')->nullOnDelete();

            $table->unique(['workspace_id', 'receipt_number']);
            $table->index(['workspace_id', 'invoice_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('receipts');
    }
};
