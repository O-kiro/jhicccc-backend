<?php

use App\Http\Controllers\Auth\HandoffController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

/*
 | Serah-terima masuk untuk admin. Ada di routes/web.php — bukan api.php —
 | karena butuh middleware sesi agar bisa membuat sesi Filament.
 |
 | Dibatasi laju untuk mempersempit peluang menebak token secara paksa.
 */
Route::get('/auth/handoff', HandoffController::class)
    ->middleware('throttle:10,1')
    ->name('admin.handoff');
