<?php

return [
    'ai_assistant' => 'AI Assistant',
    'type_draft' => 'Draft',
    'type_review' => 'Review',
    'type_suggest' => 'Suggest',
    'type_translate' => 'Translate',
    'type_explain' => 'Explain',

    // Per advisor input: docs/02_advisor_meeting_log.md Conversation 1, Decision #10
    'disclaimer' => 'This is an internal drafting aid for qualified legal professionals. It does not replace independent legal analysis.',

    'accept' => 'Accept',
    'reject' => 'Reject',
    'accepted' => 'Accepted',
    'rejected' => 'Rejected',
    'ask_placeholder' => 'Ask AI about this document...',
    'select_clause_hint' => 'Select a clause in the editor, then use this operation.',
    'generating' => 'Generating...',
    'no_interactions_yet' => 'No AI interactions yet. Try drafting or reviewing a clause.',
    'rate_limit_exceeded' => 'Daily AI usage limit reached. Please try again tomorrow.',

    // Document Generation (F-10.4)
    'generate_document' => 'Generate Document',
    'generated_via_ai' => 'AI-generated from template: :template',
    'gen_status_queued' => 'Queued',
    'gen_status_generating' => 'Generating',
    'gen_status_complete' => 'Complete',
    'gen_status_failed' => 'Failed',
    'gen_status_cancelled' => 'Cancelled',
    'generation_complete' => 'Document generated successfully',
    'generation_failed' => 'Document generation failed',

    // Templates (F-10.5)
    'template' => 'Template',
    'templates' => 'Templates',
    'review_pending' => 'Pending Review',
    'review_approved' => 'Approved',
    'review_revoked' => 'Revoked',

    // Generation History (F-10.6)
    'ai_generations' => 'AI Generations',
    'requested_by' => 'Requested By',
    'status' => 'Status',
    'tokens' => 'Tokens',
    'cost' => 'Cost',
];
