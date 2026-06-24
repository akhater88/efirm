<?php

return [
    'session' => [
        'idle_minutes' => env('ADMIN_SESSION_IDLE_MINUTES', 60),
        'absolute_hours' => env('ADMIN_SESSION_ABSOLUTE_HOURS', 12),
    ],
    'password' => [
        'min_length' => env('ADMIN_PASSWORD_MIN_LENGTH', 12),
        'reset_length' => 16,
    ],
    'locale' => [
        'default' => 'ar',
        'supported' => ['ar', 'en'],
    ],
];
