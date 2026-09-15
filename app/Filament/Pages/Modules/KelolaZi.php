<?php

namespace App\Filament\Pages\Modules;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class KelolaZi extends ModulePage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedShieldCheck;

    protected static string|UnitEnum|null $navigationGroup = 'Lainnya';

    protected static ?string $navigationLabel = 'Kelola ZI';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Kelola ZI';

    public static function getModuleSummary(): string
    {
        return 'Pengelolaan Zona Integritas madrasah.';
    }

    /**
     * @return array<int, array{title: string, description: string}>
     */
    public static function getPlannedFeatures(): array
    {
        return [];
    }
}
