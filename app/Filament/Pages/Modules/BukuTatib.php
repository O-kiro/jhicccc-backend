<?php

namespace App\Filament\Pages\Modules;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class BukuTatib extends ModulePage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedExclamationTriangle;

    protected static string|UnitEnum|null $navigationGroup = 'Lainnya';

    protected static ?string $navigationLabel = 'Buku Tatib';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Buku Tatib';

    public static function getModuleSummary(): string
    {
        return 'Buku tata tertib dan pencatatan pelanggaran.';
    }

    /**
     * @return array<int, array{title: string, description: string}>
     */
    public static function getPlannedFeatures(): array
    {
        return [];
    }
}
