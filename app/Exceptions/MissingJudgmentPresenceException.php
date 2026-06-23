<?php

namespace App\Exceptions;

/**
 * Thrown when judgment presence data is missing or incomplete.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md Conversation 2, Decision #18.
 */
class MissingJudgmentPresenceException extends \RuntimeException {}
