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
    // Isi situs publik — terbuka, tanpa token. Dibaca frontend saat
    // membangun halaman dan disegarkan tiap 60 detik.
    Route::get('public/site', PublicSiteController::class)
        ->middleware('throttle:120,1')
        ->name('public.site');

    Route::post('login', [AuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('login');

    Route::middleware('auth:student')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('me', [AuthController::class, 'me'])->name('me');
        // Dibatasi ketat: endpoint ini memeriksa sandi lama, jadi tanpa batas
        // bisa dipakai menebaknya dari sesi yang tertinggal terbuka.
        Route::post('me/password', [PasswordController::class, 'update'])
            ->middleware('throttle:5,1')
            ->name('me.password');

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
        Route::get('library', LibraryController::class)->name('library');
        Route::get('library/books', [LibraryLoanController::class, 'catalogue'])->name('library.books');
        Route::post('library/books/{book}/borrow', [LibraryLoanController::class, 'borrow'])
            ->middleware('throttle:20,1')
            ->name('library.books.borrow');
        Route::post('library/loans/{loan}/return', [LibraryLoanController::class, 'return'])
            ->middleware('throttle:20,1')
            ->name('library.loans.return');
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
});
