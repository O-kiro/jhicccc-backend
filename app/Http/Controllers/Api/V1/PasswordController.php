<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\AlumniAccount;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    /**
     * Mengganti kata sandi siswa, guru, atau alumni yang sedang masuk.
     *
     * Semua token lain dicabut setelahnya: kalau sandi diganti karena bocor,
     * perangkat yang sudah terlanjur masuk dengan sandi lama ikut keluar.
     * Token yang sedang dipakai dibiarkan supaya pemiliknya tidak terlempar
     * keluar tepat setelah menyimpan.
     */
    public function update(Request $request): JsonResponse
    {
        /** @var Student|Teacher|AlumniAccount $akun */
        $akun = $request->user();

        $data = $request->validate([
            'current_password' => ['required', 'string'],
            'password' => [
                'required',
                'string',
                'confirmed',
                'different:current_password',
                Password::min(8)->letters()->numbers(),
            ],
        ], attributes: [
            // Hanya di sini: label global "kata sandi" dipakai juga oleh login.
            'password' => 'kata sandi baru',
        ]);

        if (! Hash::check($data['current_password'], $akun->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Kata sandi saat ini salah.',
            ]);
        }

        // NISN dan NIP tercetak di kartu dan rapor — terlalu mudah ditebak.
        // Alumni tidak punya nomor induk, jadi tidak ada yang diperiksa.
        [$label, $nomor] = match (true) {
            $akun instanceof Teacher => ['NIP', $akun->nip],
            $akun instanceof AlumniAccount => ['nomor induk', null],
            default => ['NISN', $akun->nisn],
        };

        if (filled($nomor) && $data['password'] === $nomor) {
            throw ValidationException::withMessages([
                'password' => "Kata sandi tidak boleh sama dengan {$label}.",
            ]);
        }

        $akun->update(['password' => $data['password']]);

        // Token yang sedang dipakai dikecualikan bila ada; sesi yang tidak
        // berbasis token (mis. pengujian) tidak punya token berjalan.
        $sekarang = $akun->currentAccessToken();

        $dicabut = $akun->tokens()
            ->when($sekarang, fn ($q) => $q->whereKeyNot($sekarang->getKey()))
            ->delete();

        return response()->json([
            'message' => 'Kata sandi berhasil diganti.',
            'other_sessions_revoked' => $dicabut,
        ]);
    }
}
