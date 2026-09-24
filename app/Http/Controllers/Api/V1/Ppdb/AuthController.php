<?php

namespace App\Http\Controllers\Api\V1\Ppdb;

use App\Http\Controllers\Controller;
use App\Models\PpdbRegistrant;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;

/**
 * Masuk khusus PPDB, terpisah dari gerbang /masuk milik siswa, guru, dan
 * alumni: calon siswa belum punya hubungan apa pun dengan madrasah selain
 * nomor pendaftarannya.
 */
class AuthController extends Controller
{
    /** Hash bcrypt sah atas nilai acak; menyamakan waktu respons. */
    private const DUMMY_HASH = '$2y$12$.paFpCKbt.6A9bcasy4h1Oe3eV5CyuJoGorgU7XFGesYwM2VmxZkS';

    public function login(Request $request): JsonResponse
    {
        $data = $request->validate([
            'registration_number' => ['required', 'string', 'max:50'],
            'password' => ['required', 'string'],
        ], attributes: ['registration_number' => 'nomor pendaftaran']);

        $nomor = trim($data['registration_number']);

        $pendaftar = PpdbRegistrant::query()
            ->whereRaw('UPPER(registration_number) = ?', [mb_strtoupper($nomor)])
            ->first();

        // Hash::check tetap dijalankan meski nomornya tidak terdaftar, supaya
        // lama responsnya tidak membocorkan nomor mana yang ada.
        if (! Hash::check($data['password'], $pendaftar?->password ?? self::DUMMY_HASH)) {
            throw ValidationException::withMessages([
                'registration_number' => ['Nomor pendaftaran atau kata sandi salah.'],
            ]);
        }

        if (! $pendaftar->is_active) {
            throw ValidationException::withMessages([
                'registration_number' => ['Akun ini tidak aktif. Hubungi panitia PPDB.'],
            ]);
        }

        $token = $pendaftar->createToken('ppdb-web');

        return response()->json([
            'role' => 'ppdb',
            'token' => $token->plainTextToken,
            'registrant' => [
                'name' => $pendaftar->name,
                'registration_number' => $pendaftar->registration_number,
                'jalur' => $pendaftar->jalur,
            ],
        ]);
    }
}
