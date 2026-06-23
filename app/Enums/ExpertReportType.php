<?php

namespace App\Enums;

/**
 * Expert report type classification.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md
 * Conversation 1, Decisions #3 and #19 (expert report entity with 8-day objection countdown).
 */
enum ExpertReportType: string
{
    case DamagesCalculation = 'damages_calculation';
    case AccountAudit = 'account_audit';
    case TechnicalSpecification = 'technical_specification';
    case RealEstateValuation = 'real_estate_valuation';
    case Medical = 'medical';
    case HandwritingAuthentication = 'handwriting_authentication';
    case Other = 'other';

    public function label(): string
    {
        return __('expert_reports.type_'.$this->value);
    }
}
