<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('form_templates', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('name_ar');
            $table->string('name_en');
            $table->text('description')->nullable();
            $table->string('applies_to_entity_type', 100)->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('version')->default(1);
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces');
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by_user_id')->references('id')->on('users')->nullOnDelete();
        });

        Schema::create('form_template_fields', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('form_template_id', 26);
            $table->string('key', 100);
            $table->string('label_ar');
            $table->string('label_en');
            $table->string('field_type');
            $table->boolean('is_required')->default(false);
            $table->json('default_value')->nullable();
            $table->json('options')->nullable();
            $table->json('validation_rules')->nullable();
            $table->text('help_text_ar')->nullable();
            $table->text('help_text_en')->nullable();
            $table->integer('sort_order')->default(0);
            $table->boolean('is_pii')->default(false);

            $table->foreign('form_template_id')->references('id')->on('form_templates')->cascadeOnDelete();
            $table->unique(['form_template_id', 'key']);
        });

        Schema::create('form_submissions', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->string('workspace_id', 26);
            $table->string('form_template_id', 26);
            $table->integer('template_version_at_submission');
            $table->string('submittable_type', 100);
            $table->string('submittable_id', 26);
            $table->string('submitted_by_user_id', 26)->nullable();
            $table->timestamp('submitted_at');
            $table->json('values');
            $table->string('created_by_user_id', 26)->nullable();
            $table->string('updated_by_user_id', 26)->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->foreign('workspace_id')->references('id')->on('workspaces');
            $table->foreign('form_template_id')->references('id')->on('form_templates');
            $table->foreign('submitted_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('created_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('updated_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->index(['submittable_type', 'submittable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('form_submissions');
        Schema::dropIfExists('form_template_fields');
        Schema::dropIfExists('form_templates');
    }
};
