<?php

namespace App\Exceptions;

/**
 * Thrown when a court level does not have a known fixed appeal window.
 *
 * Per advisor input: docs/02_advisor_meeting_log.md Conversation 2, Decision #18.
 */
class UnsupportedCourtLevelException extends \RuntimeException {}
