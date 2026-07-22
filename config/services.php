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
    'm3' => [
        'client_id' => env('M3_CLIENTID'),
        'client_secret' => env('M3_CLIENTSECRET'),
        'token_url' => env('M3_TOKEN_URL'),
        'api_url' => env('M3_API_URL'),
    ],
    'ldap' => [
        'host' => env('LDAP_HOST'),
        'port' => env('LDAP_PORT'),
        'base_dn' => env('LDAP_BASE_DN'),
        'bind_dn' => env('LDAP_BIND_DN'),
        'bind_password' => env('LDAP_BIND_PASSWORD'),
        'user_filter' => env('LDAP_USER_FILTER'),
        'employee_id_attr' => env('LDAP_EMPLOYEE_ID_ATTR'),
    ],
];
