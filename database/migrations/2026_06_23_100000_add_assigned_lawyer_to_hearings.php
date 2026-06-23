<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('hearings', function (Blueprint $table) {
            $table->string('assigned_lawyer_user_id', 26)->nullable()->after('our_attendee_user_id');
            $table->timestamp('lawyer_assigned_at')->nullable()->after('assigned_lawyer_user_id');
            $table->string('lawyer_assigned_by_user_id', 26)->nullable()->after('lawyer_assigned_at');

            $table->foreign('assigned_lawyer_user_id')->references('id')->on('users')->onDelete('set null');
            $table->foreign('lawyer_assigned_by_user_id')->references('id')->on('users')->onDelete('set null');
        });

        // Backfill: set assigned_lawyer from Matter's active lead via matter_lawyers
        // Uses Eloquent for cross-database compatibility (SQLite in tests, MySQL in prod)
        $hearings = DB::table('hearings')
            ->whereNull('assigned_lawyer_user_id')
            ->get(['id', 'matter_id', 'created_at']);

        foreach ($hearings as $hearing) {
            $lead = DB::table('matter_lawyers')
                ->where('matter_id', $hearing->matter_id)
                ->where('role', 'lead')
                ->whereNull('unassigned_at')
                ->first();

            if ($lead) {
                DB::table('hearings')
                    ->where('id', $hearing->id)
                    ->update([
                        'assigned_lawyer_user_id' => $lead->user_id,
                        'lawyer_assigned_at' => $hearing->created_at,
                    ]);
            }
        }
    }

    public function down(): void
    {
        Schema::table('hearings', function (Blueprint $table) {
            $table->dropForeign(['assigned_lawyer_user_id']);
            $table->dropForeign(['lawyer_assigned_by_user_id']);
            $table->dropColumn(['assigned_lawyer_user_id', 'lawyer_assigned_at', 'lawyer_assigned_by_user_id']);
        });
    }
};
