<?php

/**
 * F-FIX-01.8 — JordanCourtsSeeder CSV-driven tests.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md
 * Conversation 2, Decision #24.
 */

use App\Models\Workspace;
use Database\Seeders\JordanCourtsSeeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

it('seeder handles missing CSV gracefully', function () {
    $csvPath = database_path('seeders/data/jordan_courts.csv');
    $backupPath = $csvPath.'.bak';
    $existed = file_exists($csvPath);

    if ($existed) {
        rename($csvPath, $backupPath);
    }

    try {
        Log::shouldReceive('warning')
            ->once()
            ->withArgs(fn ($msg) => str_contains($msg, 'jordan_courts.csv'));

        $seeder = new JordanCourtsSeeder;
        $seeder->run('some-workspace-id');

        expect(true)->toBeTrue();
    } finally {
        if ($existed) {
            rename($backupPath, $csvPath);
        }
    }
});

it('seeder reads CSV and upserts courts correctly', function () {
    $csvPath = database_path('seeders/data/jordan_courts.csv');
    expect(file_exists($csvPath))->toBeTrue();

    $workspace = Workspace::factory()->create();

    Log::shouldReceive('info')
        ->once()
        ->withArgs(fn ($msg) => str_contains($msg, 'upserted'));

    $seeder = new JordanCourtsSeeder;
    $seeder->run($workspace->id);

    // Verify courts were seeded into this workspace
    $count = DB::table('courts')
        ->where('workspace_id', $workspace->id)
        ->where('jurisdiction_country', 'JO')
        ->count();

    expect($count)->toBeGreaterThan(0);

    // Verify a known court exists
    $ammanCourt = DB::table('courts')
        ->where('workspace_id', $workspace->id)
        ->where('name_ar', 'محكمة بداية عمان (قصر العدل)')
        ->first();

    expect($ammanCourt)->not->toBeNull()
        ->and($ammanCourt->jurisdiction_governorate)->toBe('Amman');
});
