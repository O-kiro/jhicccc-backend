<?php

namespace App\Models\Concerns;

use App\Services\RevalidasiSitus;

/**
 * Dipasang pada model konten My Website: setiap simpan atau hapus meminta
 * situs publik memperbarui cache-nya.
 */
trait MemicuRevalidasiSitus
{
    protected static function bootMemicuRevalidasiSitus(): void
    {
        $minta = fn () => app(RevalidasiSitus::class)->minta();

        static::saved($minta);
        static::deleted($minta);
    }
}
