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
}
