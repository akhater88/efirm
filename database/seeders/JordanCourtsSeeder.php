<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;

/**
 * CSV-driven seeder for Jordanian court hierarchy.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md
 * Conversation 2, Decision #24 — Khaldoun to send curated CSV of Jordanian court hierarchy.
 *
 * [PENDING-CSV-FROM-ADVISOR] — Khaldoun to send curated CSV.
 * Current CSV has header + sample rows for initial development.
 */
class JordanCourtsSeeder extends Seeder
{
    /**
     * Map CSV court_tier values to CourtType enum values.
     */
    private const TIER_TO_COURT_TYPE = [
        'Cassation' => 'cassation',
        'High Court' => 'first_instance', // High Court of Justice maps to first_instance
        'Appeal' => 'appeal',
        'First Instance' => 'first_instance',
        'Magistrate' => 'magistrate',
    ];

    /**
     * Run the seeder.
     *
     * @param  string|null  $workspaceId  Required — courts are workspace-scoped.
     *                                    Pass null to skip seeding (e.g. when called without context).
     */
    public function run(?string $workspaceId = null): void
    {
        $csvPath = database_path('seeders/data/jordan_courts.csv');

        if (! file_exists($csvPath)) {
            Log::warning('[WARNING] jordan_courts.csv not found or empty — skipping court seeding. Pending CSV from advisor (Decision #24).');

            return;
        }

        $handle = fopen($csvPath, 'r');
        if ($handle === false) {
            Log::warning('[WARNING] jordan_courts.csv could not be opened — skipping court seeding. Pending CSV from advisor (Decision #24).');

            return;
        }

        $header = fgetcsv($handle);
        if ($header === false || empty($header)) {
            Log::warning('[WARNING] jordan_courts.csv is empty — skipping court seeding. Pending CSV from advisor (Decision #24).');
            fclose($handle);

            return;
        }

        if ($workspaceId === null) {
            Log::warning('[WARNING] JordanCourtsSeeder requires a workspace_id — skipping. Call with ->run($workspaceId).');
            fclose($handle);

            return;
        }

        $rowCount = 0;
        while (($row = fgetcsv($handle)) !== false) {
            if (count($row) < 5) {
                continue;
            }

            // Map CSV columns: id, court_tier, court_name_ar, court_name_en, governorate, jurisdiction_type
            $nameAr = $row[2] ?? '';
            $governorate = $row[4] ?? '';
            $courtTier = $row[1] ?? 'unknown';
            $courtType = self::TIER_TO_COURT_TYPE[$courtTier] ?? 'first_instance';

            if (empty($nameAr)) {
                continue;
            }

            // Upsert by (workspace_id, name_ar, jurisdiction_governorate) — idempotent
            $exists = DB::table('courts')
                ->where('workspace_id', $workspaceId)
                ->where('name_ar', $nameAr)
                ->where('jurisdiction_governorate', $governorate)
                ->exists();

            if ($exists) {
                DB::table('courts')
                    ->where('workspace_id', $workspaceId)
                    ->where('name_ar', $nameAr)
                    ->where('jurisdiction_governorate', $governorate)
                    ->update([
                        'name_en' => $row[3] ?? null,
                        'jurisdiction_country' => 'JO',
                        'court_type' => $courtType,
                        'notes' => 'Seeded from jordan_courts.csv — court_tier: '.$courtTier,
                        'updated_at' => now(),
                    ]);
            } else {
                DB::table('courts')->insert([
                    'id' => (string) Str::ulid(),
                    'workspace_id' => $workspaceId,
                    'name_ar' => $nameAr,
                    'name_en' => $row[3] ?? null,
                    'court_type' => $courtType,
                    'jurisdiction_country' => 'JO',
                    'jurisdiction_governorate' => $governorate,
                    'city' => $governorate, // Use governorate as city default
                    'notes' => 'Seeded from jordan_courts.csv — court_tier: '.$courtTier,
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }

            $rowCount++;
        }

        fclose($handle);

        if ($rowCount === 0) {
            Log::warning('[WARNING] jordan_courts.csv contained no valid data rows — skipping court seeding. Pending CSV from advisor (Decision #24).');
        } else {
            Log::info("[INFO] JordanCourtsSeeder: upserted {$rowCount} courts from CSV.");
        }
    }
}
