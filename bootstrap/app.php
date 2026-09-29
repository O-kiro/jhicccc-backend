<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Di server, aplikasi ini selalu di belakang reverse proxy — tidak
        // pernah menghadap internet langsung. Tanpa ini Laravel mengabaikan
        // X-Forwarded-*, menganggap permintaannya http, lalu `request()
        // ->isSecure()` bernilai salah walau pengunjung membuka https.
        //
        // '*' aman di sini karena port aplikasi hanya terbuka untuk
        // 127.0.0.1: satu-satunya yang bisa menjangkaunya memang proxy itu.
        $middleware->trustProxies(at: '*');
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        $exceptions->shouldRenderJsonWhen(
            fn (Request $request) => $request->is('api/*') || $request->expectsJson(),
        );
    })->create();
