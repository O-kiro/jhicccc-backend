<?php

use App\Http\Controllers\Api\V1\AnnouncementController;
use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Api\V1\CourseController;
use App\Http\Controllers\Api\V1\ExamAnswerController;
use App\Http\Controllers\Api\V1\ExamController;
use App\Http\Controllers\Api\V1\ExamSessionController;
use App\Http\Controllers\Api\V1\ExamSubmissionController;
use App\Http\Controllers\Api\V1\ForumController;
use App\Http\Controllers\Api\V1\ForumLikeController;
use App\Http\Controllers\Api\V1\ForumReplyController;
use App\Http\Controllers\Api\V1\ForumThreadController;
use App\Http\Controllers\Api\V1\ForumThreadShowController;
use App\Http\Controllers\Api\V1\Guru\JadwalController as GuruJadwalController;
use App\Http\Controllers\Api\V1\Guru\JurnalController as GuruJurnalController;
use App\Http\Controllers\Api\V1\Guru\KelasController as GuruKelasController;
use App\Http\Controllers\Api\V1\Guru\ModulController as GuruModulController;
use App\Http\Controllers\Api\V1\Guru\NilaiController as GuruNilaiController;
use App\Http\Controllers\Api\V1\Guru\OverviewController as GuruOverviewController;
use App\Http\Controllers\Api\V1\Guru\ProfilController as GuruProfilController;
use App\Http\Controllers\Api\V1\LibraryController;
use App\Http\Controllers\Api\V1\LibraryLoanController;
use App\Http\Controllers\Api\V1\ModuleCompletionController;
use App\Http\Controllers\Api\V1\OverviewController;
use App\Http\Controllers\Api\V1\PasswordController;
use App\Http\Controllers\Api\V1\PublicSiteController;
use App\Http\Controllers\Api\V1\ReportCardController;
use App\Http\Controllers\Api\V1\ReportCardPdfController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Portal Siswa & Portal Guru — v1
|--------------------------------------------------------------------------
|
| Dikonsumsi oleh portal Next.js (/siswa/* dan /guru/*). Autentikasi memakai
| token Sanctum pada guard "student" atau "teacher"; kirim sebagai header:
|
|     Authorization: Bearer <token>
|
| Endpoint bersama menerima keduanya. Batas lajunya memakai limiter bernama
| (lihat AppServiceProvider) supaya siswa #1 dan guru #1 tidak berbagi jatah.
|
*/

Route::prefix('v1')->name('api.v1.')->group(function (): void {
    // Isi situs publik — terbuka, tanpa token. Dibaca frontend saat
    // membangun halaman dan disegarkan tiap 60 detik.
    Route::get('public/site', PublicSiteController::class)
        ->middleware('throttle:120,1')
        ->name('public.site');

    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login');

    // Dipakai bersama: perpustakaan, ganti sandi, dan keluar.
    Route::middleware('auth:student,teacher')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('me/password', [PasswordController::class, 'update'])
            ->middleware('throttle:portal-sandi')
            ->name('me.password');

        Route::get('library', LibraryController::class)->name('library');
        Route::get('library/books', [LibraryLoanController::class, 'catalogue'])->name('library.books');
        Route::post('library/books/{book}/borrow', [LibraryLoanController::class, 'borrow'])
            ->middleware('throttle:portal-tulis')
            ->name('library.books.borrow');
        Route::post('library/loans/{loan}/return', [LibraryLoanController::class, 'return'])
            ->middleware('throttle:portal-tulis')
            ->name('library.loans.return');
    });

    Route::middleware('auth:student')->group(function (): void {
        Route::get('me', [AuthController::class, 'me'])->name('me');

        Route::get('overview', OverviewController::class)->name('overview');
        Route::get('report-card', ReportCardController::class)->name('report-card');
        Route::get('report-card/pdf', ReportCardPdfController::class)->name('report-card.pdf');
        Route::get('announcements', [AnnouncementController::class, 'index'])->name('announcements.index');

        Route::get('courses', CourseController::class)->name('courses');
        Route::post('courses/modules/{module}/toggle', [ModuleCompletionController::class, 'toggle'])
            ->middleware('throttle:60,1')
            ->name('courses.modules.toggle');
        Route::get('exams', ExamController::class)->name('exams');
        Route::get('exam-session', ExamSessionController::class)->name('exam-session');
        Route::post('exam-session/answers', [ExamAnswerController::class, 'store'])
            ->name('exam-session.answers.store');
        Route::post('exam-session/finish', [ExamSubmissionController::class, 'store'])
            ->name('exam-session.finish');
        Route::get('forum', ForumController::class)->name('forum');
        Route::post('forum/threads', [ForumThreadController::class, 'store'])
            ->middleware('throttle:10,1')
            ->name('forum.threads.store');
        Route::get('forum/threads/{thread}', ForumThreadShowController::class)
            ->name('forum.threads.show');
        Route::post('forum/threads/{thread}/replies', [ForumReplyController::class, 'store'])
            ->middleware('throttle:20,1')
            ->name('forum.threads.replies.store');
        Route::delete('forum/replies/{reply}', [ForumReplyController::class, 'destroy'])
            ->middleware('throttle:20,1')
            ->name('forum.replies.destroy');
        Route::post('forum/threads/{thread}/like', [ForumLikeController::class, 'store'])
            ->middleware('throttle:60,1')
            ->name('forum.threads.like');
    });

    Route::prefix('guru')->name('guru.')->middleware('auth:teacher')->group(function (): void {
        Route::get('me', GuruProfilController::class)->name('me');
        Route::get('overview', GuruOverviewController::class)->name('overview');
        Route::get('jadwal', GuruJadwalController::class)->name('jadwal');

        Route::get('jurnal', [GuruJurnalController::class, 'index'])->name('jurnal.index');
        Route::post('jurnal', [GuruJurnalController::class, 'store'])
            ->middleware('throttle:portal-tulis')
            ->name('jurnal.store');
        Route::delete('jurnal/{journal}', [GuruJurnalController::class, 'destroy'])
            ->middleware('throttle:portal-tulis')
            ->name('jurnal.destroy');

        Route::get('kelas', GuruKelasController::class)->name('kelas');
        Route::post('kelas/{course}/modul', [GuruModulController::class, 'store'])
            ->middleware('throttle:portal-tulis')
            ->name('kelas.modul.store');
        Route::put('modul/{module}', [GuruModulController::class, 'update'])
            ->middleware('throttle:portal-tulis')
            ->name('modul.update');
        Route::delete('modul/{module}', [GuruModulController::class, 'destroy'])
            ->middleware('throttle:portal-tulis')
            ->name('modul.destroy');

        Route::get('nilai', [GuruNilaiController::class, 'index'])->name('nilai.index');
        Route::post('nilai', [GuruNilaiController::class, 'store'])
            ->middleware('throttle:portal-tulis')
            ->name('nilai.store');
    });
});
