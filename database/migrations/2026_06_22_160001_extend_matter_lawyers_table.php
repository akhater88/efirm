<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('matter_lawyers', function (Blueprint $table) {
            $table->timestamp('assigned_at')->nullable()->after('role');
            $table->string('assigned_by_user_id', 26)->nullable()->after('assigned_at');
            $table->timestamp('unassigned_at')->nullable()->after('assigned_by_user_id');
            $table->string('unassigned_by_user_id', 26)->nullable()->after('unassigned_at');
            $table->text('notes')->nullable()->after('unassigned_by_user_id');
            $table->timestamp('backfilled_at')->nullable()->after('notes');

            $table->foreign('assigned_by_user_id')->references('id')->on('users')->nullOnDelete();
            $table->foreign('unassigned_by_user_id')->references('id')->on('users')->nullOnDelete();

            $table->index(['matter_id', 'role'], 'matter_lawyers_matter_role_idx');
        });
    }

    public function down(): void
    {
        Schema::table('matter_lawyers', function (Blueprint $table) {
            $table->dropIndex('matter_lawyers_matter_role_idx');
            $table->dropForeign(['assigned_by_user_id']);
            $table->dropForeign(['unassigned_by_user_id']);
            $table->dropColumn([
                'assigned_at',
                'assigned_by_user_id',
                'unassigned_at',
                'unassigned_by_user_id',
                'notes',
                'backfilled_at',
            ]);
        });
    }
};
