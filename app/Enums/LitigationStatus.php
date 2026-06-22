<?php

namespace App\Enums;

// [PROVISIONAL-FOUNDER-DECIDED] [HARD-STOP-LAWYER-REQUIRED]
enum LitigationStatus: string
{
    case PreFiling = 'pre_filing';
    case Filed = 'filed';
    case InEvidence = 'in_evidence';
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
            self::Filed => __('litigation.status_filed'),
            self::InEvidence => __('litigation.status_in_evidence'),
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
            self::Filed => 'info',
            self::InEvidence => 'warning',
            self::InJudgment => 'warning',
            self::Appealed => 'danger',
            self::ClosedWon => 'success',
            self::ClosedLost => 'danger',
            self::Settled => 'success',
            self::Withdrawn => 'gray',
        };
    }
}
