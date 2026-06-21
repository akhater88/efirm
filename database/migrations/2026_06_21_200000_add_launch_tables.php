<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('onboarding_state')->nullable()->after('preferred_locale');
        });

        Schema::table('workspaces', function (Blueprint $table) {
            $table->string('billing_status')->default('trial')->after('default_locale');
            $table->timestamp('trial_ends_at')->nullable()->after('billing_status');
        });

        Schema::create('legal_acceptances', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('user_id', 26);
            $table->string('document_type'); // terms, privacy, dpa, ai_disclaimer
            $table->string('version', 20);
            $table->timestamp('accepted_at');
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->unique(['user_id', 'document_type', 'version'], 'legal_accept_unique');
        });

        Schema::create('feedback', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('user_id', 26);
            $table->string('type')->default('general'); // general, bug, feature, complaint
            $table->text('message');
            $table->string('page_url')->nullable();
            $table->string('status')->default('new'); // new, reviewed, resolved
            $table->string('created_by_user_id', 26)->nullable();
            $table->timestamps();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->index('workspace_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('feedback');
        Schema::dropIfExists('legal_acceptances');
        Schema::table('workspaces', function (Blueprint $table) {
            $table->dropColumn(['billing_status', 'trial_ends_at']);
        });
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('onboarding_state');
        });
    }
};
