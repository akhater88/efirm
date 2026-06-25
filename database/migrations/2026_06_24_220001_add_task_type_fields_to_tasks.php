<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->string('task_type_id', 26)->nullable()->after('tags');
            $table->json('custom_field_values')->nullable()->after('task_type_id');

            $table->foreign('task_type_id')->references('id')->on('task_types')->onDelete('set null');
            $table->index('task_type_id');
        });
    }

    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            $table->dropIndex(['task_type_id']);
            $table->dropForeign(['task_type_id']);
            $table->dropColumn(['task_type_id', 'custom_field_values']);
        });
    }
};
