<?php

namespace App\Filament\Pages\Modules;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class Ptsp extends ModulePage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingStorefront;

    protected static string|UnitEnum|null $navigationGroup = 'Lainnya';

    protected static ?string $navigationLabel = 'PTSP';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'PTSP';

    public static function getModuleSummary(): string
    {
        return 'Pelayanan Terpadu Satu Pintu.';
    }

    /**
     * @return array<int, array{title: string, description: string}>
     */
    public static function getPlannedFeatures(): array
    {
        return [];
    }
}
