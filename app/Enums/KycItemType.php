<?php

namespace App\Enums;

// [ADVISOR-REVIEW-RECOMMENDED] — item types should be reviewed by legal advisor
// for Levant regulatory alignment
enum KycItemType: string
{
    // Person items
    case NationalId = 'national_id';
    case Passport = 'passport';
    case AddressProof = 'address_proof';
    case TaxId = 'tax_id';
    case SanctionsCheck = 'sanctions_check';
    case PepCheck = 'pep_check';
    case SourceOfFundsDeclaration = 'source_of_funds_declaration';

    // Organization items
    case CommercialRegistration = 'commercial_registration';
    case ArticlesOfAssociation = 'articles_of_association';
    case BeneficialOwnerDeclaration = 'beneficial_owner_declaration';
    case BankCertificate = 'bank_certificate';
    case AuthorizedSignatoriesList = 'authorized_signatories_list';

    public function label(): string
    {
        return __('kyc.item_type_'.$this->value);
    }

    public function forContactType(): string
    {
        return match ($this) {
            self::NationalId, self::Passport, self::AddressProof,
            self::TaxId, self::SanctionsCheck, self::PepCheck,
            self::SourceOfFundsDeclaration => 'person',
            self::CommercialRegistration, self::ArticlesOfAssociation,
            self::BeneficialOwnerDeclaration, self::BankCertificate,
            self::AuthorizedSignatoriesList => 'organization',
        };
    }

    /**
     * Get the default items for a given contact type.
     *
     * @return self[]
     */
    public static function forType(string $contactType): array
    {
        return array_filter(self::cases(), fn (self $case) => $case->forContactType() === $contactType);
    }
}
