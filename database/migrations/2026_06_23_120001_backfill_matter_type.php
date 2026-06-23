<?php

use App\Models\Matter;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        $updated = 0;

        Matter::withoutGlobalScopes()
            ->whereNull('matter_type')
            ->chunkById(100, function ($matters) use (&$updated) {
                foreach ($matters as $matter) {
                    $type = $matter->is_litigation
                        ? 'commercial_litigation'
                        : 'commercial_contracts';

                    DB::table('matters')
                        ->where('id', $matter->id)
                        ->update(['matter_type' => $type]);

                    $updated++;
                }
            });

        if (app()->runningInConsole()) {
            echo "Backfilled matter_type for {$updated} matters.\n";
        }
    }

    public function down(): void
    {
        // No-op: leave matter_type values in place
    }
};
