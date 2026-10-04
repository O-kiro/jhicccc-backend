<?php

/*
 * Moderasi forum otomatis lewat Gemini. Kosongkan GEMINI_API_KEY untuk
 * mematikannya — postingan langsung tayang seperti sebelumnya.
 */
return [
    'gemini' => [
        'key' => env('GEMINI_API_KEY'),
        'model' => env('GEMINI_MODEL', 'gemini-2.5-flash'),
        // Penulis menunggu jawaban ini sebelum postingannya tayang; lewat dari
        // ini, postingan tetap tayang dan ditandai "belum dicek".
        'timeout' => (int) env('GEMINI_TIMEOUT', 8),
    ],
];
