<?php

namespace App\Enums;

/**
 * Litigation status enum for matter lifecycle tracking.
 *
 * Extended per advisor input: docs/02_advisor_meeting_log.md
 * Conversation 1, Decisions #1 (fee payment, notification, referred to expert).
 */
// [PROVISIONAL-FOUNDER-DECIDED] [HARD-STOP-LAWYER-REQUIRED]
enum LitigationStatus: string
{
    case PreFiling = 'pre_filing';
    case FeePaymentAndRegistration = 'fee_payment_and_registration'; // Decision #1 — قيد الدعوى ودفع الرسوم
    case Filed = 'filed';
    case NotificationPending = 'notification_pending'; // Decision #1 — تبليغ
    case InEvidence = 'in_evidence';
    case ReferredToExpert = 'referred_to_expert'; // Decision #1 — الإحالة للخبرة
    case InJudgment = 'in_judgment';
    case Appealed = 'appealed';
    case ClosedWon = 'closed_won';
    case ClosedLost = 'closed_lost';
    case Settled = 'settled';
    case Withdrawn = 'withdrawn';

    public function label(): string
    {
        return match ($this) {
            self::PreFiling => __('litigation.status_pre_filing'),
            self::FeePaymentAndRegistration => __('litigation.status_fee_payment_and_registration'),
            self::Filed => __('litigation.status_filed'),
            self::NotificationPending => __('litigation.status_notification_pending'),
            self::InEvidence => __('litigation.status_in_evidence'),
            self::ReferredToExpert => __('litigation.status_referred_to_expert'),
            self::InJudgment => __('litigation.status_in_judgment'),
            self::Appealed => __('litigation.status_appealed'),
            self::ClosedWon => __('litigation.status_closed_won'),
            self::ClosedLost => __('litigation.status_closed_lost'),
            self::Settled => __('litigation.status_settled'),
            self::Withdrawn => __('litigation.status_withdrawn'),
        };
    }

    public function color(): string
    {
        return match ($this) {
            self::PreFiling => 'gray',
            self::FeePaymentAndRegistration => 'info',
            self::Filed => 'info',
            self::NotificationPending => 'warning',
            self::InEvidence => 'warning',
            self::ReferredToExpert => 'warning',
            self::InJudgment => 'warning',
            self::Appealed => 'danger',
            self::ClosedWon => 'success',
            self::ClosedLost => 'danger',
            self::Settled => 'success',
            self::Withdrawn => 'gray',
        };
    }
}
