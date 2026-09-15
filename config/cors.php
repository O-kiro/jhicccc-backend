<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cross-Origin Resource Sharing (CORS) Configuration
    |--------------------------------------------------------------------------
    |
    | Portal Next.js berjalan di origin berbeda (port 3000) dari Laravel
    | (port 8000), jadi origin-nya harus didaftarkan di sini. Autentikasi
    | memakai token Bearer, bukan cookie, sehingga supports_credentials
    | tidak perlu dinyalakan.
    |
    */

    'paths' => ['api/*'],

    'allowed_methods' => ['*'],

    'allowed_origins' => array_filter(
        array_map('trim', explode(',', (string) env('FRONTEND_URLS', 'http://localhost:3000')))
    ),

    'allowed_origins_patterns' => [],

    'allowed_headers' => ['*'],

    'exposed_headers' => [],

    'max_age' => 0,

    'supports_credentials' => false,

];
