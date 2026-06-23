<?php

/**
 * F-FIX-01.1 — Litigation enum extensions.
 *
 * No actual ALTER needed — enum values are stored as strings in MySQL.
 * The PHP enum classes (LitigationStatus, HearingType) have been updated directly.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md
 * Conversation 1, Decisions #1 (litigation status extensions) and #2 (hearing type split).
 */

use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        // No schema change required — enum values are stored as VARCHAR strings.
        // LitigationStatus: added fee_payment_and_registration, notification_pending, referred_to_expert
        // HearingType: added plaintiff_evidence, defendant_evidence, notification_session
    }

    public function down(): void
    {
        // No rollback needed — enum values are PHP-only; no schema change was made.
    }
};
