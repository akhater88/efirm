<?php

namespace App\Enums;

// [PROVISIONAL-FOUNDER-DECIDED] [HARD-STOP-LAWYER-REQUIRED]
enum DecisionType: string
{
    case InterimOrder = 'interim_order';
    case ProceduralRuling = 'procedural_ruling';
    case ExpertAppointment = 'expert_appointment';
    case EvidenceRuling = 'evidence_ruling';
    case PartialJudgment = 'partial_judgment';
    case FinalJudgment = 'final_judgment';
    case AppealDecision = 'appeal_decision';
    case EnforcementOrder = 'enforcement_order';
    case Other = 'other';

    public function label(): string
    {
        return match ($this) {
            self::InterimOrder => __('litigation.decision_type_interim_order'),
            self::ProceduralRuling => __('litigation.decision_type_procedural_ruling'),
            self::ExpertAppointment => __('litigation.decision_type_expert_appointment'),
            self::EvidenceRuling => __('litigation.decision_type_evidence_ruling'),
            self::PartialJudgment => __('litigation.decision_type_partial_judgment'),
            self::FinalJudgment => __('litigation.decision_type_final_judgment'),
            self::AppealDecision => __('litigation.decision_type_appeal_decision'),
            self::EnforcementOrder => __('litigation.decision_type_enforcement_order'),
            self::Other => __('litigation.decision_type_other'),
        };
    }
}
