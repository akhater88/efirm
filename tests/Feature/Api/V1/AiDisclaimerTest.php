<?php

/**
 * F-FIX-01.7 — AI Disclaimer text tests.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md
 * Conversation 1, Decision #10.
 */
it('AI disclaimer matches expected EN text', function () {
    app()->setLocale('en');

    expect(__('ai.disclaimer'))
        ->toBe('This is an internal drafting aid for qualified legal professionals. It does not replace independent legal analysis.');
});

it('AI disclaimer matches expected AR text', function () {
    app()->setLocale('ar');

    expect(__('ai.disclaimer'))
        ->toBe('هذه أداة صياغة داخلية للمحامين المؤهلين. لا تغني عن التحليل القانوني المستقل.');
});
