<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calendar_integrations', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('user_id', 26);
            $table->string('provider'); // google, outlook
            $table->string('calendar_id', 255)->nullable();
            $table->text('oauth_access_token');
            $table->text('oauth_refresh_token');
            $table->timestamp('oauth_expires_at')->nullable();
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

        Schema::create('external_calendar_events', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('user_id', 26);
            $table->string('calendar_integration_id', 26);
            $table->string('provider_event_id', 255);
            $table->string('title', 500);
            $table->text('description')->nullable();
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->string('timezone', 50)->nullable();
            $table->boolean('is_all_day')->default(false);
            $table->json('attendees')->nullable();
            $table->string('location', 500)->nullable();
            $table->string('linked_matter_id', 26)->nullable();
            $table->string('linked_hearing_id', 26)->nullable();
            $table->timestamp('last_synced_at')->nullable();
            $table->timestamps();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->cascadeOnDelete();
            $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
            $table->foreign('calendar_integration_id')->references('id')->on('calendar_integrations')->cascadeOnDelete();
            $table->foreign('linked_matter_id')->references('id')->on('matters')->nullOnDelete();
            $table->foreign('linked_hearing_id')->references('id')->on('hearings')->nullOnDelete();

            $table->unique(['calendar_integration_id', 'provider_event_id'], 'cal_int_provider_event_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('external_calendar_events');
        Schema::dropIfExists('calendar_integrations');
    }
};
