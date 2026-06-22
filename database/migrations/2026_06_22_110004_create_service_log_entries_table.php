<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_log_entries', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('matter_id', 26);
            $table->string('served_party_contact_id', 26);
            $table->string('service_method');
            $table->date('service_date');
            $table->text('service_address')->nullable();
            $table->string('served_by_name', 200)->nullable();
            $table->string('served_to_recipient_name', 200)->nullable();
            $table->string('proof_document_id', 26)->nullable();
            $table->string('status');
            $table->text('notes')->nullable();
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('matter_id')->references('id')->on('matters')->onDelete('cascade');
            $table->foreign('served_party_contact_id')->references('id')->on('contacts')->onDelete('cascade');
            $table->foreign('proof_document_id')->references('id')->on('documents')->nullOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_log_entries');
    }
};
