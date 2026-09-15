<?php

namespace App\Filament\Pages\Modules;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class Persuratan extends ModulePage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedEnvelope;

    protected static string|UnitEnum|null $navigationGroup = 'Lainnya';

    protected static ?string $navigationLabel = 'Persuratan';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Persuratan';

    public static function getModuleSummary(): string
    {
        return 'Administrasi surat masuk dan surat keluar.';
    }

    /**
     * @return array<int, array{title: string, description: string}>
     */
    public static function getPlannedFeatures(): array
    {
        return [];
    }
}
