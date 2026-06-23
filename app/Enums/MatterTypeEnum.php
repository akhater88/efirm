<?php

namespace App\Enums;

/**
 * Forked Matter creation workflow per advisor input from Khaldoun Khater,
 * docs/02_advisor_meeting_log.md Conversation 3.5, Decision #26.
 */
enum MatterTypeEnum: string
{
    // Transactional
    case CommercialContracts = 'commercial_contracts';
    case MnA = 'mna';
    case CorporateGovernance = 'corporate_governance';
    case Securities = 'securities';
    case GeneralCounsel = 'general_counsel';
    case Advisory = 'advisory';
    case RealEstateTransaction = 'real_estate_transaction';
    case EmploymentDrafting = 'employment_drafting';

    // Litigation
    case CommercialLitigation = 'commercial_litigation';
    case CivilLitigation = 'civil_litigation';
    case Enforcement = 'enforcement';
    case Arbitration = 'arbitration';
    case LaborDispute = 'labor_dispute';
    case AdministrativeDispute = 'administrative_dispute';

    private const TRANSACTIONAL_CASES = [
        'commercial_contracts',
        'mna',
        'corporate_governance',
        'securities',
        'general_counsel',
        'advisory',
        'real_estate_transaction',
        'employment_drafting',
    ];

    public function isTransactional(): bool
    {
        return in_array($this->value, self::TRANSACTIONAL_CASES, true);
    }

    public function isLitigation(): bool
    {
        return ! $this->isTransactional();
    }

    public function track(): string
    {
        return $this->isLitigation() ? 'litigation' : 'transactional';
    }

    public function label(): string
    {
        return __('matter_types.'.$this->value);
    }
}
