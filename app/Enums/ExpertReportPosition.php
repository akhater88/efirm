<?php

namespace App\Enums;

/**
 * Our position on an expert report.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md
 * Conversation 1, Decisions #3 and #19.
 */
enum ExpertReportPosition: string
{
    case NotYetReviewed = 'not_yet_reviewed';
    case Accepted = 'accepted';
    case Objected = 'objected';
    case PartiallyAccepted = 'partially_accepted';

    public function label(): string
    {
        return __('expert_reports.position_'.$this->value);
    }
}
