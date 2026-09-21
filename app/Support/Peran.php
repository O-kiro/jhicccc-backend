<?php

namespace App\Support;

use App\Models\User;

/**
 * Peran pengguna panel admin dan menu yang boleh dibuka masing-masing.
 *
 * Satu-satunya tempat pemetaan ini: resource dan halaman panel memeriksanya
 * lewat App\Filament\Concerns\DibatasiPeran. Unit izinnya adalah "modul" —
 * sama dengan grup navigasi, kecuali beberapa menu di grup "Lainnya" yang
 * punya modul sendiri karena pemiliknya berbeda (Konseling milik BK,
 * Persuratan milik tata usaha, dst.).
 */
final class Peran
{
    public const SUPER_ADMIN = 'super_admin';

    public const BK = 'bk';

    /** @var array<string, string> */
    public const LABEL = [
        self::SUPER_ADMIN => 'Admin Utama',
        'kurikulum' => 'Wakasek Kurikulum',
        'kesiswaan' => 'Wakasek Kesiswaan',
        self::BK => 'Guru BK',
        'tata_usaha' => 'Tata Usaha',
        'humas' => 'Humas',
        'pustakawan' => 'Pustakawan',
    ];

    /**
     * Modul per peran. Admin Utama tidak didaftarkan di sini: ia membuka
     * semuanya, termasuk modul Pengguna.
     *
     * @var array<string, list<string>>
     */
    public const MODUL = [
        'kurikulum' => ['Data Master', 'Akademik'],
        'kesiswaan' => ['Data Master', 'Kesiswaan', 'Absensi'],
        self::BK => ['Kesiswaan', 'Konseling'],
        'tata_usaha' => ['Data Master', 'Keuangan', 'Sarana & Prasarana', 'Persuratan', 'Kelola ZI', 'Sync Data'],
        'humas' => ['Humas', 'Konten', 'My Website'],
        'pustakawan' => ['E-Library'],
    ];

    public static function sah(?string $peran): bool
    {
        return $peran !== null && array_key_exists($peran, self::LABEL);
    }

    public static function bolehModul(?User $user, string $modul): bool
    {
        if (! $user || ! self::sah($user->role)) {
            return false;
        }

        if ($user->role === self::SUPER_ADMIN) {
            return true;
        }

        return in_array($modul, self::MODUL[$user->role] ?? [], true);
    }

    /**
     * Catatan konseling bertanda rahasia hanya untuk Guru BK — bahkan Admin
     * Utama tidak. Admin Utama bisa memberi dirinya peran BK bila memang
     * perlu, dan perubahan itu tercatat.
     */
    public static function bolehBacaKonselingRahasia(?User $user): bool
    {
        return $user?->role === self::BK;
    }
}
