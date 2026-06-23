<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $matters = DB::table('matters')
            ->whereNotNull('lead_lawyer_id')
            ->whereNull('deleted_at')
            ->get(['id', 'lead_lawyer_id', 'created_at', 'created_by_user_id']);

        $backfilledCount = 0;

        foreach ($matters as $matter) {
            $existingLead = DB::table('matter_lawyers')
                ->where('matter_id', $matter->id)
                ->where('role', 'lead')
                ->exists();

            if (! $existingLead) {
                DB::table('matter_lawyers')->insert([
                    'matter_id' => $matter->id,
                    'user_id' => $matter->lead_lawyer_id,
                    'role' => 'lead',
                    'assigned_at' => $matter->created_at,
                    'assigned_by_user_id' => $matter->created_by_user_id,
                    'backfilled_at' => now(),
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
                $backfilledCount++;
            }
        }

        if ($backfilledCount > 0) {
            echo "Backfilled {$backfilledCount} lead lawyer assignments from matters.lead_lawyer_id.\n";
        } else {
            echo "No lead lawyer assignments needed backfilling.\n";
        }
    }

    public function down(): void
    {
        $deleted = DB::table('matter_lawyers')
            ->whereNotNull('backfilled_at')
            ->delete();

        echo "Removed {$deleted} backfilled matter_lawyers rows.\n";
    }
};
