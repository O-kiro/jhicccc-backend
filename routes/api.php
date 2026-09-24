<?php

use App\Http\Controllers\Api\V1\Alumni\BeasiswaController as AlumniBeasiswaController;
use App\Http\Controllers\Api\V1\Alumni\ForumController as AlumniForumController;
use App\Http\Controllers\Api\V1\Alumni\ForumLikeController as AlumniForumLikeController;
use App\Http\Controllers\Api\V1\Alumni\ForumReplyController as AlumniForumReplyController;
use App\Http\Controllers\Api\V1\Alumni\ForumThreadController as AlumniForumThreadController;
use App\Http\Controllers\Api\V1\Alumni\ForumThreadShowController as AlumniForumThreadShowController;
use App\Http\Controllers\Api\V1\Alumni\OverviewController as AlumniOverviewController;
use App\Http\Controllers\Api\V1\Alumni\ProfilController as AlumniProfilController;
use App\Http\Controllers\Api\V1\Alumni\StatistikController as AlumniStatistikController;
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
use App\Http\Controllers\Api\V1\Guru\BahanAjarController as GuruBahanAjarController;
use App\Http\Controllers\Api\V1\Guru\JadwalController as GuruJadwalController;
use App\Http\Controllers\Api\V1\Guru\JurnalController as GuruJurnalController;
use App\Http\Controllers\Api\V1\Guru\JurnalHarianController as GuruJurnalHarianController;
use App\Http\Controllers\Api\V1\Guru\KelasController as GuruKelasController;
use App\Http\Controllers\Api\V1\Guru\ModulAjarController as GuruModulAjarController;
use App\Http\Controllers\Api\V1\Guru\ModulController as GuruModulController;
use App\Http\Controllers\Api\V1\Guru\NilaiController as GuruNilaiController;
use App\Http\Controllers\Api\V1\Guru\OverviewController as GuruOverviewController;
use App\Http\Controllers\Api\V1\Guru\ProfilController as GuruProfilController;
use App\Http\Controllers\Api\V1\Guru\RdmController as GuruRdmController;
use App\Http\Controllers\Api\V1\Guru\TatibController as GuruTatibController;
use App\Http\Controllers\Api\V1\LibraryController;
use App\Http\Controllers\Api\V1\LibraryLoanController;
use App\Http\Controllers\Api\V1\ModuleCompletionController;
use App\Http\Controllers\Api\V1\OverviewController;
use App\Http\Controllers\Api\V1\PasswordController;
use App\Http\Controllers\Api\V1\Ppdb\AuthController as PpdbAuthController;
use App\Http\Controllers\Api\V1\Ppdb\BerkasController as PpdbBerkasController;
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

    // Masuk PPDB terpisah dari gerbang /masuk: yang dipakai nomor
    // pendaftaran, bukan identitas warga madrasah.
    Route::post('ppdb/login', [PpdbAuthController::class, 'login'])
        ->middleware('throttle:6,1')
        ->name('ppdb.login');

    // Keluar dan ganti sandi berlaku untuk ketiga portal.
    Route::middleware('auth:student,teacher,alumni')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::post('me/password', [PasswordController::class, 'update'])
            ->middleware('throttle:portal-sandi')
            ->name('me.password');
    });

    // Perpustakaan dipakai bersama siswa dan guru; alumni tidak meminjam buku.
    Route::middleware('auth:student,teacher')->group(function (): void {
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

        // Modul ajar (TP/ATP) — perangkat pembelajaran milik guru.
        Route::get('modul-ajar', [GuruModulAjarController::class, 'index'])->name('modul-ajar.index');
        Route::post('modul-ajar', [GuruModulAjarController::class, 'store'])
            ->middleware('throttle:portal-tulis')->name('modul-ajar.store');
        Route::put('modul-ajar/{plan}', [GuruModulAjarController::class, 'update'])
            ->middleware('throttle:portal-tulis')->name('modul-ajar.update');
        Route::delete('modul-ajar/{plan}', [GuruModulAjarController::class, 'destroy'])
            ->middleware('throttle:portal-tulis')->name('modul-ajar.destroy');

        Route::get('bahan-ajar', [GuruBahanAjarController::class, 'index'])->name('bahan-ajar.index');
        Route::post('bahan-ajar', [GuruBahanAjarController::class, 'store'])
            ->middleware('throttle:portal-tulis')->name('bahan-ajar.store');
        Route::put('bahan-ajar/{material}', [GuruBahanAjarController::class, 'update'])
            ->middleware('throttle:portal-tulis')->name('bahan-ajar.update');
        Route::delete('bahan-ajar/{material}', [GuruBahanAjarController::class, 'destroy'])
            ->middleware('throttle:portal-tulis')->name('bahan-ajar.destroy');

        Route::get('jurnal-harian', [GuruJurnalHarianController::class, 'index'])->name('jurnal-harian.index');
        Route::post('jurnal-harian', [GuruJurnalHarianController::class, 'store'])
            ->middleware('throttle:portal-tulis')->name('jurnal-harian.store');
        Route::delete('jurnal-harian/{activity}', [GuruJurnalHarianController::class, 'destroy'])
            ->middleware('throttle:portal-tulis')->name('jurnal-harian.destroy');

        Route::get('rdm', [GuruRdmController::class, 'index'])->name('rdm.index');
        Route::post('rdm', [GuruRdmController::class, 'store'])
            ->middleware('throttle:portal-tulis')->name('rdm.store');
        Route::delete('rdm/{upload}', [GuruRdmController::class, 'destroy'])
            ->middleware('throttle:portal-tulis')->name('rdm.destroy');
        Route::post('rdm/catatan', [GuruRdmController::class, 'storeCatatan'])
            ->middleware('throttle:portal-tulis')->name('rdm.catatan.store');
        Route::put('rdm/catatan/{feedback}', [GuruRdmController::class, 'updateCatatan'])
            ->middleware('throttle:portal-tulis')->name('rdm.catatan.update');
        Route::delete('rdm/catatan/{feedback}', [GuruRdmController::class, 'destroyCatatan'])
            ->middleware('throttle:portal-tulis')->name('rdm.catatan.destroy');

        Route::get('tatib', [GuruTatibController::class, 'index'])->name('tatib.index');
        Route::post('tatib', [GuruTatibController::class, 'store'])
            ->middleware('throttle:portal-tulis')->name('tatib.store');

        Route::get('nilai', [GuruNilaiController::class, 'index'])->name('nilai.index');
        Route::post('nilai', [GuruNilaiController::class, 'store'])
            ->middleware('throttle:portal-tulis')
            ->name('nilai.store');
    });

    Route::prefix('ppdb')->name('ppdb.')->middleware('auth:ppdb')->group(function (): void {
        Route::post('logout', [AuthController::class, 'logout'])->name('logout');
        Route::get('berkas', [PpdbBerkasController::class, 'index'])->name('berkas.index');
        Route::post('berkas', [PpdbBerkasController::class, 'store'])
            ->middleware('throttle:portal-tulis')
            ->name('berkas.store');
        Route::delete('berkas/{document}', [PpdbBerkasController::class, 'destroy'])
            ->middleware('throttle:portal-tulis')
            ->name('berkas.destroy');
    });

    Route::prefix('alumni')->name('alumni.')->middleware('auth:alumni')->group(function (): void {
        Route::get('me', AlumniProfilController::class)->name('me');
        Route::get('overview', AlumniOverviewController::class)->name('overview');
        Route::get('beasiswa', AlumniBeasiswaController::class)->name('beasiswa');
        Route::get('statistik', AlumniStatistikController::class)->name('statistik');

        Route::get('forum', AlumniForumController::class)->name('forum');
        Route::post('forum/threads', [AlumniForumThreadController::class, 'store'])
            ->middleware('throttle:portal-tulis')
            ->name('forum.threads.store');
        Route::get('forum/threads/{thread}', AlumniForumThreadShowController::class)
            ->name('forum.threads.show');
        Route::post('forum/threads/{thread}/replies', [AlumniForumReplyController::class, 'store'])
            ->middleware('throttle:portal-tulis')
            ->name('forum.threads.replies.store');
        Route::delete('forum/replies/{reply}', [AlumniForumReplyController::class, 'destroy'])
            ->middleware('throttle:portal-tulis')
            ->name('forum.replies.destroy');
        Route::post('forum/threads/{thread}/like', [AlumniForumLikeController::class, 'store'])
            ->middleware('throttle:portal-tulis')
            ->name('forum.threads.like');
    });
});
