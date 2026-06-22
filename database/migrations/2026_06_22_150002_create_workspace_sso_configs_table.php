<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('workspace_sso_configs', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26)->unique();
            $table->string('provider_type'); // saml2, oidc
            $table->string('provider_name', 100);
            $table->string('idp_metadata_url', 500)->nullable();
            $table->text('idp_metadata_xml')->nullable();
            $table->string('idp_entity_id', 255);
            $table->string('idp_sso_url', 500);
            $table->text('idp_certificate');
            $table->string('sp_entity_id', 255);
            $table->json('attribute_mapping');
            $table->string('enforce_for_domain', 255)->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();

            $table->foreign('workspace_id')->references('id')->on('workspaces')->cascadeOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by_user_id')->references('id')->on('users')->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('workspace_sso_configs');
    }
};
