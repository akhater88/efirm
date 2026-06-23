<?php

/**
 * F-FIX-01.5 — PDPL consent fields on workspaces.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md
 * Conversation 1, Decision #8 (Jordan PDPL Law 24/2023 cross-border data transfer consent)
 * and Conversation 2, Decision #21 (confirmed approach).
 *
 * NOTE: Blocking middleware is NOT added yet. Fields are stored but not enforced
 * until a paid lawyer drafts the actual consent text.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            $table->boolean('pdpl_consent_obtained')->default(false)->after('updated_by_user_id');
            $table->timestamp('pdpl_consent_date')->nullable()->after('pdpl_consent_obtained');
            $table->string('pdpl_consent_text_version', 50)->nullable()->after('pdpl_consent_date');
        });
    }

    public function down(): void
    {
        Schema::table('workspaces', function (Blueprint $table) {
            $table->dropColumn(['pdpl_consent_obtained', 'pdpl_consent_date', 'pdpl_consent_text_version']);
        });
    }
};
