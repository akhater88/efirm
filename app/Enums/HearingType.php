<?php

namespace App\Enums;

// [PROVISIONAL-FOUNDER-DECIDED] [HARD-STOP-LAWYER-REQUIRED]
enum HearingType: string
{
    case FirstSession = 'first_session';
    case Evidence = 'evidence';
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
            self::ExpertWitness => __('litigation.hearing_type_expert_witness'),
            self::WitnessTestimony => __('litigation.hearing_type_witness_testimony'),
            self::FinalArguments => __('litigation.hearing_type_final_arguments'),
            self::Judgment => __('litigation.hearing_type_judgment'),
            self::Enforcement => __('litigation.hearing_type_enforcement'),
            self::Other => __('litigation.hearing_type_other'),
        };
    }
}
