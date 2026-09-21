<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;

class PasswordController extends Controller
{
    /**
     * Mengganti kata sandi siswa yang sedang masuk.
     *
     * Semua token lain dicabut setelahnya: kalau sandi diganti karena bocor,
     * perangkat yang sudah terlanjur masuk dengan sandi lama ikut keluar.
     * Token yang sedang dipakai dibiarkan supaya siswa tidak terlempar keluar
     * tepat setelah menyimpan.
     */
    public function update(Request $request): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user();

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

        if (! Hash::check($data['current_password'], $student->password)) {
            throw ValidationException::withMessages([
                'current_password' => 'Kata sandi saat ini salah.',
            ]);
        }

        // NISN tercetak di kartu dan rapor — terlalu mudah ditebak.
        if ($data['password'] === $student->nisn) {
            throw ValidationException::withMessages([
                'password' => 'Kata sandi tidak boleh sama dengan NISN.',
            ]);
        }

        $student->update(['password' => $data['password']]);

        $dicabut = $student->tokens()
            ->whereKeyNot($student->currentAccessToken()->getKey())
            ->delete();

        return response()->json([
            'message' => 'Kata sandi berhasil diganti.',
            'other_sessions_revoked' => $dicabut,
        ]);
    }
}
