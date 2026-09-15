<?php

use App\Http\Controllers\Api\V1\AnnouncementController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\OverviewController;
use App\Http\Controllers\Api\V1\ReportCardController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Portal Siswa — v1
|--------------------------------------------------------------------------
|
| Dikonsumsi oleh portal Next.js (/siswa/*). Autentikasi memakai token
| Sanctum pada guard "student"; kirim sebagai header:
|
|     Authorization: Bearer <token>
|
*/

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login');

    Route::middleware('auth:student')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('me', [AuthController::class, 'me'])->name('me');

        Route::get('overview', OverviewController::class)->name('overview');
        Route::get('report-card', ReportCardController::class)->name('report-card');
        Route::get('announcements', [AnnouncementController::class, 'index'])->name('announcements.index');
    });
});
