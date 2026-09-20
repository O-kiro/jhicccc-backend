<?php

namespace App\Filament\Pages;

use BackedEnum;
use Filament\Pages\Dashboard as BaseDashboard;
use Filament\Support\Icons\Heroicon;

/**
 * Menggantikan dasbor bawaan Filament supaya judul dan ikonnya mengikuti
 * dokumen MAKOBADIG ("Dashboard Admin").
 */
class Dashboard extends BaseDashboard
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSquares2x2;

    protected static ?string $navigationLabel = 'Dashboard Admin';

    protected static ?string $title = 'Dashboard Admin';

    public function getSubheading(): ?string
    {
        return 'Ringkasan data madrasah — MAKOBADIG';
    }

    /**
     * Satu kolom, bukan dua seperti bawaan Filament.
     *
     * DasborMadrasah mengatur sendiri grid di dalamnya (hero, tiga kartu
     * ringkasan, lalu dua kolom) agar sama dengan portal siswa. Dengan dasbor
     * dua kolom, widget itu hanya mendapat setengah lebar — properti
     * $columnSpan = 'full' tidak terbaca karena Filament v5 merender dasbor
     * lewat sistem Schema, bukan grid widget lama.
     */
    public function getColumns(): int|array
    {
        return 1;
    }
}
