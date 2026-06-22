<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_integrations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('user_id', 26);
            $table->string('provider'); // outlook, gmail
            $table->string('email_address');
            $table->text('oauth_access_token');
            $table->text('oauth_refresh_token');
            $table->timestamp('oauth_expires_at')->nullable();
            $table->json('scopes_granted')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();
            $table->softDeletes();
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by_user_id')->references('id')->on('users')->nullOnDelete();

            $table->unique(['user_id', 'provider']);
        });

        Schema::create('email_attachments', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('attached_to_type', 100);
            $table->string('attached_to_id', 26);
            $table->string('email_integration_id', 26);
            $table->string('email_provider_id', 255);
            $table->string('subject', 500);
            $table->string('from_address', 255);
            $table->string('from_name')->nullable();
            $table->json('to_addresses');
            $table->json('cc_addresses')->nullable();
            $table->dateTime('received_at');
            $table->text('body_snippet');
            $table->boolean('has_attachments')->default(false);
            $table->json('attachment_files')->nullable();
            $table->boolean('is_outbound')->default(false);
            $table->timestamps();
            $table->softDeletes();
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->cascadeOnDelete();
            $table->foreign('email_integration_id')->references('id')->on('email_integrations')->cascadeOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by_user_id')->references('id')->on('users')->nullOnDelete();

            $table->index(['attached_to_type', 'attached_to_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_attachments');
        Schema::dropIfExists('email_integrations');
    }
};
