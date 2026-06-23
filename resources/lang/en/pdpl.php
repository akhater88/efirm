<?php

// Per advisor input: docs/02_advisor_meeting_log.md Conversation 1, Decisions #8 and #21
// Jordan Personal Data Protection Law No. 24 of 2023

return [
    'consent_title' => 'Data Processing Consent',
    'consent_subtitle' => 'Jordan Personal Data Protection Law (No. 24 of 2023)',

    // [PENDING-PAID-LAWYER-DRAFT] — placeholder consent text; must be replaced by
    // professionally drafted text before production use. See validation/06_legal_docs/introduction_pending.md
    'consent_text' => 'By using this service, you consent to the processing and storage of your data, '
        .'including cross-border transfer to servers located in Frankfurt, Germany, '
        .'in accordance with Jordan\'s Personal Data Protection Law No. 24 of 2023. '
        .'Your data will be processed solely for the purpose of providing legal workspace services. '
        .'You may withdraw your consent at any time by contacting support.',

    'consent_obtained' => 'Consent Obtained',
    'consent_date' => 'Consent Date',
    'consent_text_version' => 'Consent Text Version',
    'consent_not_yet_obtained' => 'PDPL consent has not been obtained for this workspace.',
    'consent_required_notice' => 'Cross-border data transfer requires explicit prior consent under Jordan PDPL Law 24/2023.',
];
