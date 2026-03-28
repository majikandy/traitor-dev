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
        'key' => env('POSTMARK_API_KEY'),
    ],

    'resend' => [
        'key' => env('RESEND_API_KEY'),
    ],

    'ses' => [
        'key' => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'cpanel' => [
        'host'           => env('CPANEL_HOST'),
        'user'           => env('CPANEL_USER'),
        'token'          => env('CPANEL_TOKEN'),
        'root_domain'    => env('CPANEL_ROOT_DOMAIN'),
        'preview_domain' => env('CPANEL_PREVIEW_DOMAIN'),
        'staging_domain' => env('CPANEL_STAGING_DOMAIN'),
    ],

    'github' => [
        'app_id'          => env('GITHUB_APP_ID'),
        'app_slug'        => env('GITHUB_APP_SLUG'),
        'private_key_b64' => env('GITHUB_APP_PRIVATE_KEY_BASE64'),
        'webhook_secret'  => env('GITHUB_WEBHOOK_SECRET'),
    ],

];
