<?php

return [

    /*
     * Situs publik Next.js. Tiap kali konten My Website disimpan, Laravel
     * memanggil webhook ini supaya situs langsung memakai isi terbaru.
     * Kosongkan salah satunya untuk mematikan — situs tetap memperbarui
     * sendiri paling lama tiap 60 detik.
     */
    'situs' => [
        'revalidate_url' => env('SITUS_REVALIDATE_URL'),
        'revalidate_secret' => env('SITUS_REVALIDATE_SECRET'),
    ],

    /*
    |--------------------------------------------------------------------------
    | Third Party Services
    |--------------------------------------------------------------------------
    |
    | This file is for storing the credentials for third party services such
    | as Resend, Postmark, AWS, and more. This file provides the de facto
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

];
