<?php

namespace App\Services;

use App\Enums\JudgmentPresence;
use App\Exceptions\MissingJudgmentPresenceException;
use App\Exceptions\UnconfirmedRegulationException;
use App\Exceptions\UnsupportedCourtLevelException;
use App\Models\CourtReview;
use Illuminate\Support\Carbon;

/**
 * Court-level appeal window logic per advisor input from Khaldoun Khater
 * (Al-Dujani Office, Amman), 2026-06-23 — see docs/02_advisor_meeting_log.md
 * Conversation 2, Decision #18.
 *
 * CRITICAL: This service replaces the prior hardcoded 30-day assumption.
 * A 10-day Magistrate deadline rendered as 30 days = missed appeal =
 * potential malpractice exposure for the firm using this product.
 */
class AppealDeadlineService
{
    public function calculate(CourtReview $review): Carbon
    {
        $courtLevel = $review->matter->court_level;

        if ($courtLevel === null || $courtLevel->appealWindowDays() === null) {
            throw new UnsupportedCourtLevelException(
                'Court level does not have a known fixed appeal window. Requires manual input.'
            );
        }

        $windowDays = $courtLevel->appealWindowDays();

        $presence = $review->judgment_presence;

        if ($presence === null) {
            throw new MissingJudgmentPresenceException(
                'Judgment presence is required to calculate the appeal deadline.'
            );
        }

        $startDate = match ($presence) {
            JudgmentPresence::Wijahi => $review->decision_date->copy()->addDay(),
            JudgmentPresence::MithlaWijahi => $this->startDateForDeemedInPresence($review),
            JudgmentPresence::Ghyabi => throw new UnconfirmedRegulationException(
                'Pure default (ghyabi) appeal window not confirmed. See docs/02_advisor_meeting_log.md Decision #22.'
            ),
        };

        return $startDate->addDays($windowDays);
    }

    private function startDateForDeemedInPresence(CourtReview $review): Carbon
    {
        if ($review->notified_date === null) {
            throw new MissingJudgmentPresenceException(
                'notified_date is NULL for deemed-in-presence'
            );
        }

        return $review->notified_date->copy()->addDay();
    }
}
