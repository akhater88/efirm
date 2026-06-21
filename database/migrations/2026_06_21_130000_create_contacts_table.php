<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('contacts', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->enum('type', ['person', 'organization']);

            // Person fields
            $table->string('first_name')->nullable();
            $table->string('middle_name')->nullable();
            $table->string('last_name')->nullable();

            // Organization field
            $table->string('organization_name')->nullable();

            // Computed display name (indexed for search)
            $table->string('display_name');

            // Contact info
            $table->string('email')->nullable();
            $table->string('phone')->nullable();

            // Classification
            $table->string('nationality', 2)->nullable();
            $table->string('tax_registration_number')->nullable();

            // Address (structured)
            $table->string('address_line_1')->nullable();
            $table->string('address_line_2')->nullable();
            $table->string('city')->nullable();
            $table->string('country', 2)->nullable();

            // Flags
            $table->boolean('is_client')->default(false);
            $table->boolean('is_counterparty')->default(false);

            // Metadata
            $table->text('notes')->nullable();
            $table->json('labels')->nullable();

            // Self-referential (Person → Organization)
            $table->string('parent_organization_id', 26)->nullable();

            // Audit
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            // Foreign keys
            $table->foreign('workspace_id')
                ->references('id')->on('workspaces')
                ->onDelete('cascade');
            $table->foreign('parent_organization_id')
                ->references('id')->on('contacts')
                ->onDelete('set null');
            $table->foreign('created_by_user_id')
                ->references('id')->on('users')
                ->onDelete('set null');
            $table->foreign('updated_by_user_id')
                ->references('id')->on('users')
                ->onDelete('set null');

            // Indexes
            $table->index('workspace_id');
            $table->index('type');
            $table->index(['workspace_id', 'is_client'], 'contacts_workspace_client_idx');
            $table->index(['workspace_id', 'is_counterparty'], 'contacts_workspace_counterparty_idx');
        });

        // FULLTEXT index with ngram parser for Arabic search (MySQL only — skipped on SQLite in tests)
        if (DB::getDriverName() === 'mysql') {
            DB::statement('ALTER TABLE contacts ADD FULLTEXT INDEX contacts_display_name_fulltext (display_name) WITH PARSER ngram');
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('contacts');
    }
};
