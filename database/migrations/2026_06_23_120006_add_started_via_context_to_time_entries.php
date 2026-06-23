<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            $table->string('started_via_context', 30)->nullable()->after('currency');
            // Allow nullable ended_at and description for quick-timer entries (F-FIX-02.4)
            $table->text('description')->nullable()->change();
            $table->dateTime('ended_at')->nullable()->change();
            $table->unsignedInteger('duration_minutes')->default(0)->change();
        });
    }

    public function down(): void
    {
        Schema::table('time_entries', function (Blueprint $table) {
            $table->dropColumn('started_via_context');
            $table->text('description')->nullable(false)->change();
            $table->dateTime('ended_at')->nullable(false)->change();
            $table->unsignedInteger('duration_minutes')->default(null)->change();
        });
    }
};
