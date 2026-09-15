<?php

namespace App\Filament\Pages\Modules;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class SyncData extends ModulePage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedArrowPath;

    protected static string|UnitEnum|null $navigationGroup = 'Lainnya';

    protected static ?string $navigationLabel = 'Sync Data';

    protected static ?int $navigationSort = 6;

    protected static ?string $title = 'Sync Data';

    public static function getModuleSummary(): string
    {
        return 'Sinkronisasi data dengan sistem pusat.';
    }

    /**
     * @return array<int, array{title: string, description: string}>
     */
    public static function getPlannedFeatures(): array
    {
        return [
            [
                'title' => 'Periksa Update',
                'description' => 'Tarik pembaruan data dari sumber eksternal.',
            ],
        ];
    }
}
