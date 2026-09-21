<?php

namespace App\Filament\Concerns;

use App\Support\Peran;

/**
 * Membatasi resource atau halaman panel pada peran yang berhak.
 *
 * Filament memanggil canAccess() untuk menampilkan menu DAN saat halaman
 * dibuka (membalas 403 bila salah), jadi URL yang diketik langsung pun
 * tertolak — bukan cuma menunya yang disembunyikan.
 *
 * Modulnya bawaan dari grup navigasi. Kelas yang modulnya berbeda dari
 * grupnya (mis. Konseling di grup "Lainnya") menimpa modulAkses().
 */
trait DibatasiPeran
{
    public static function modulAkses(): string
    {
        return (string) static::getNavigationGroup();
    }

    public static function canAccess(): bool
    {
        return Peran::bolehModul(auth()->user(), static::modulAkses());
    }

    /**
     * Untuk resource: canViewAny() juga dipakai pencarian global dan relasi,
     * jadi disamakan dengan canAccess() supaya tidak ada pintu samping.
     */
    public static function canViewAny(): bool
    {
        return static::canAccess();
    }
}
