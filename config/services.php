<?php

declare(strict_types=1);

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
        'key'    => env('AWS_ACCESS_KEY_ID'),
        'secret' => env('AWS_SECRET_ACCESS_KEY'),
        'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
    ],

    'resend' => [
        'key' => env('RESEND_KEY'),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel'              => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'github' => [
        'enabled'       => env('GITHUB_ENABLED', false),
        'client_id'     => env('GITHUB_CLIENT_ID'),
        'client_secret' => env('GITHUB_CLIENT_SECRET'),
        'redirect'      => config('app.url') . '/auth/social/github/callback',
    ],

    'gitlab' => [
        'enabled'       => env('GITLAB_ENABLED', false),
        'client_id'     => env('GITLAB_CLIENT_ID'),
        'client_secret' => env('GITLAB_CLIENT_SECRET'),
        'redirect'      => config('app.url') . '/auth/social/gitlab/callback',
    ],

    'bitbucket' => [
        'enabled'       => env('BITBUCKET_ENABLED', false),
        'client_id'     => env('BITBUCKET_CLIENT_ID'),
        'client_secret' => env('BITBUCKET_CLIENT_SECRET'),
        'redirect'      => config('app.url') . '/auth/social/bitbucket/callback',
    ],

    'google' => [
        'enabled'       => env('GOOGLE_ENABLED', false),
        'client_id'     => env('GOOGLE_CLIENT_ID'),
        'client_secret' => env('GOOGLE_CLIENT_SECRET'),
        'redirect'      => config('app.url') . '/auth/social/google/callback',
    ],

    'azure' => [
        'enabled'       => env('AZURE_ENABLED', false),
        'client_id'     => env('AZURE_CLIENT_ID'),
        'client_secret' => env('AZURE_CLIENT_SECRET'),
        'redirect'      => config('app.url') . '/auth/social/azure/callback',
        'tenant'        => env('AZURE_TENANT_ID'),
        'proxy'         => env('AZURE_PROXY'),
    ],

    'slack' => [
        'enabled'       => env('SLACK_ENABLED', false),
        'client_id'     => env('SLACK_CLIENT_ID'),
        'client_secret' => env('SLACK_CLIENT_SECRET'),
        'redirect'      => config('app.url') . '/auth/social/slack/callback',
    ],

];
