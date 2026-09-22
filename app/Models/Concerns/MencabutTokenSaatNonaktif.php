<?php

namespace App\Models\Concerns;

/**
 * Menonaktifkan akun ikut mengeluarkannya dari semua perangkat.
 *
 * Pemeriksaan is_active hanya terjadi saat masuk; tanpa ini token yang sudah
 * terlanjur terbit tetap berlaku sampai pemiliknya keluar sendiri.
 */
trait MencabutTokenSaatNonaktif
{
    protected static function bootMencabutTokenSaatNonaktif(): void
    {
        static::updated(function (self $akun): void {
            if ($akun->wasChanged('is_active') && ! $akun->is_active) {
                $akun->tokens()->delete();
            }
        });
    }
}
