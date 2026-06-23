<?php

namespace App\Enums;

/**
 * Hearing type enum for court session classification.
 *
 * Extended per advisor input: docs/02_advisor_meeting_log.md
 * Conversation 1, Decision #2 (split evidence into plaintiff/defendant; add notification session).
 */
// [PROVISIONAL-FOUNDER-DECIDED] [HARD-STOP-LAWYER-REQUIRED]
enum HearingType: string
{
    case FirstSession = 'first_session';
    // [DEPRECATED] — split into plaintiff_evidence and defendant_evidence per Decision #2. Kept for backward compat.
    case Evidence = 'evidence';
    case PlaintiffEvidence = 'plaintiff_evidence'; // Decision #2 — بينات المدعي
    case DefendantEvidence = 'defendant_evidence'; // Decision #2 — بينات المدعى عليه
    case NotificationSession = 'notification_session'; // Decision #2 — جلسة تبليغ
    case ExpertWitness = 'expert_witness';
    case WitnessTestimony = 'witness_testimony';
    case FinalArguments = 'final_arguments';
    case Judgment = 'judgment';
    case Enforcement = 'enforcement';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::FirstSession => __('litigation.hearing_type_first_session'),
            self::Evidence => __('litigation.hearing_type_evidence'),
            self::PlaintiffEvidence => __('litigation.hearing_type_plaintiff_evidence'),
            self::DefendantEvidence => __('litigation.hearing_type_defendant_evidence'),
            self::NotificationSession => __('litigation.hearing_type_notification_session'),
            self::ExpertWitness => __('litigation.hearing_type_expert_witness'),
            self::WitnessTestimony => __('litigation.hearing_type_witness_testimony'),
            self::FinalArguments => __('litigation.hearing_type_final_arguments'),
            self::Judgment => __('litigation.hearing_type_judgment'),
            self::Enforcement => __('litigation.hearing_type_enforcement'),
            self::Other => __('litigation.hearing_type_other'),
        };
    }
}
