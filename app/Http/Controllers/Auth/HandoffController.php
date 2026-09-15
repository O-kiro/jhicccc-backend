<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Api\V1\AuthController;
use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;

/**
 * Menukar tautan serah-terima sekali pakai menjadi sesi Filament.
 *
 * Halaman masuk tunggal berada di portal Next.js, sedangkan panel admin
 * berjalan di atas sesi Laravel pada origin berbeda — sehingga cookie sesi
 * tidak bisa dipasang lintas origin. Tautan ini menjembatani keduanya.
 *
 * Pengamanannya berlapis:
 *   - token acak 64 karakter, disimpan sebagai hash SHA-256 (bocornya isi
 *     cache tidak langsung memberi tautan yang bisa dipakai);
 *   - berumur 60 detik;
 *   - sekali pakai — Cache::pull mengambil sekaligus menghapus;
 *   - sesi diregenerasi setelah masuk untuk mencegah session fixation;
 *   - rutenya dibatasi laju di routes/web.php.
 */
class HandoffController extends Controller
{
    public function __invoke(Request $request): RedirectResponse
    {
        $token = $request->string('token')->toString();

        if ($token === '') {
            return $this->reject();
        }

        $userId = Cache::pull(AuthController::handoffKey($token));

        if ($userId === null) {
            return $this->reject();
        }

        if (! Auth::guard('web')->loginUsingId($userId)) {
            return $this->reject();
        }

        $request->session()->regenerate();

        return redirect()->intended('/admin');
    }

    private function reject(): RedirectResponse
    {
        return redirect('/admin/login')->withErrors([
            'email' => 'Tautan masuk sudah kedaluwarsa atau telah dipakai. Silakan masuk kembali.',
        ]);
    }
}
