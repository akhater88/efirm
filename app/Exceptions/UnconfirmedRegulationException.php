<?php

namespace App\Exceptions;

/**
 * Thrown when the applicable regulation has not been confirmed by legal counsel.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md Conversation 2, Decision #18.
 */
class UnconfirmedRegulationException extends \RuntimeException {}
