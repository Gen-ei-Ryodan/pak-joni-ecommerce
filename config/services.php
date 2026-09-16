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

    'google' => [
        'maps_key' => env('GOOGLE_MAPS_API_KEY', ''),
    ],

    'slack' => [
        'notifications' => [
            'bot_user_oauth_token' => env('SLACK_BOT_USER_OAUTH_TOKEN'),
            'channel' => env('SLACK_BOT_USER_DEFAULT_CHANNEL'),
        ],
    ],

    'midtrans' => [
        'server_key' => env('MIDTRANS_SERVER_KEY'),
        'client_key' => env('MIDTRANS_CLIENT_KEY'),
        'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
        'merchant_id' => env('MIDTRANS_MERCHANT_ID'),
        'is_sanitized' => true,
        'is_3ds' => true,
    ],

    'ocbc' => [
        'base_url' => env('OCBC_BASE_URL', 'https://tst.yokke.co.id:7778'),
        'client_key' => env('OCBC_CLIENT_KEY'),
        'client_secret' => env('OCBC_CLIENT_SECRET'),
        'merchant_id' => env('OCBC_MERCHANT_ID'),
        'terminal_id' => env('OCBC_TERMINAL_ID'),
        'partner_id' => env('OCBC_PARTNER_ID'),
        'channel_id' => env('OCBC_CHANNEL_ID', '02'),
        'private_key_path' => env('OCBC_PRIVATE_KEY_PATH'),
        'notify_url' => env('OCBC_NOTIFY_URL', '/v1.0/qr/qr-mpm-notify'),
        'member_bank' => env('OCBC_MEMBER_BANK', '999'),
        'signature_encoding' => env('OCBC_SIGNATURE_ENCODING', 'base64'),
        'notify_signature_mode' => env('OCBC_NOTIFY_SIGNATURE_MODE', 'hmac'),
        'notify_public_key_path' => env('OCBC_NOTIFY_PUBLIC_KEY_PATH'),
        'notify_signature_encoding' => env('OCBC_NOTIFY_SIGNATURE_ENCODING', 'base64'),
    ],

    'biteship' => [
        'api_key' => env('BITESHIP_API_KEY'),
        'base_url' => 'https://api.biteship.com',
    ],

];
