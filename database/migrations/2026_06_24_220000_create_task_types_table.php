<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('task_types', function (Blueprint $table) {
            $table->ulid('id')->primary();
            $table->foreignUlid('workspace_id')->constrained()->cascadeOnDelete();
            $table->string('name_en', 100);
            $table->string('name_ar', 100);
            $table->string('slug', 50);
            $table->string('icon', 50)->default('clipboard');
            $table->string('color', 20)->default('#0D5C2E');
            $table->foreignUlid('default_workflow_id')->nullable()->constrained('task_workflows')->nullOnDelete();
            $table->json('custom_fields')->nullable();
            $table->boolean('is_active')->default(true);
            $table->integer('sort_order')->default(0);
            $table->timestamps();
            $table->softDeletes();
            $table->unique(['workspace_id', 'slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('task_types');
    }
};
