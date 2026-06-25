<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Founding Firm Badge
    |--------------------------------------------------------------------------
    |
    | Controls visibility of the "First 50 founding firms" promotional badge
    | on the Pro pricing tier. Set to false once the threshold is reached.
    |
    */
    'founding_firm_badge_enabled' => env('MARKETING_FOUNDING_BADGE_ENABLED', true),

    /*
    |--------------------------------------------------------------------------
    | Pricing Tiers
    |--------------------------------------------------------------------------
    */
    'pricing_tiers' => [
        'starter' => [
            'slug' => 'starter',
            'price_usd' => 20,
            'jod_equivalent' => 14,
            'seats' => '1-5',
            'matters' => '50',
            'storage_gb' => 5,
            'ai_requests' => 100,
            'audit_retention' => '90 days',
            'trust_ledger' => false,
            'pdpl_compliance' => true,
            'frankfurt_residency' => true,
        ],
        'pro' => [
            'slug' => 'pro',
            'price_usd' => 25,
            'jod_equivalent' => 18,
            'seats' => '1-15',
            'matters' => '200',
            'storage_gb' => 25,
            'ai_requests' => 500,
            'audit_retention' => '1 year',
            'trust_ledger' => true,
            'pdpl_compliance' => true,
            'frankfurt_residency' => true,
        ],
        'enterprise' => [
            'slug' => 'enterprise',
            'price_usd' => 30,
            'jod_equivalent' => 21,
            'seats' => 'Unlimited',
            'matters' => 'Unlimited',
            'storage_gb' => 100,
            'ai_requests' => 'Unlimited',
            'audit_retention' => 'Unlimited',
            'trust_ledger' => true,
            'pdpl_compliance' => true,
            'frankfurt_residency' => true,
        ],
    ],

    /*
    |--------------------------------------------------------------------------
    | JOD Exchange Rate
    |--------------------------------------------------------------------------
    |
    | Fixed JOD/USD rate for display purposes. Updated manually quarterly.
    |
    */
    'jod_rate' => 0.709,
    'jod_rate_updated_at' => '2026-06-24',

];
