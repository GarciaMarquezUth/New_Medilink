<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Mailgun, Postmark, AWS and more. This file provides the de facto
    | location for this type of information, allowing packages to have
    | a conventional file to locate the various service credentials.
    |
    */

    'postmark' => [
        'token' => env('POSTMARK_TOKEN'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'gmail' => [
        'enabled' => env('GMAIL_NOTIFICATIONS_ENABLED', false),
        'client_id' => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect_uri' => env('GOOGLE_REDIRECT_URI', 'http://127.0.0.1:8000/google/gmail/callback'),
        'refresh_token' => env('GOOGLE_REFRESH_TOKEN'),
        'from_address' => env('GMAIL_FROM_ADDRESS'),
        'from_name' => env('GMAIL_FROM_NAME', env('APP_NAME', 'Clinica')),
        'scope' => 'https://www.googleapis.com/auth/gmail.send',
        'reminder_hours' => (int) env('GMAIL_REMINDER_HOURS', 24),
    ],

];
