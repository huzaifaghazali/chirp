<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Application Rate Limits
    |--------------------------------------------------------------------------
    */

    // Likes: 30 per minute to prevent spam/engagement manipulation
    'likes' => [
        'max_attempts' => 30,
        'decay_minutes' => 1,
    ],

    // Reports: 5 per hour to prevent abuse of moderation system
    'reports' => [
        'max_attempts' => 5,
        'decay_minutes' => 60,
    ],

    // Searches: 60 per minute to prevent scraping/load issues
    'searches' => [
        'max_attempts' => 60,
        'decay_minutes' => 1,
    ],

    // Chirps: 10 per minute to prevent spam
    'chirps' => [
        'max_attempts' => 10,
        'decay_minutes' => 1,
    ],

    // Login attempts: 5 per minute (security)
    'login' => [
        'max_attempts' => 5,
        'decay_minutes' => 1,
    ],
];
