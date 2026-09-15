<?php

namespace App\Filament\Pages\Modules;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class Konseling extends ModulePage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedChatBubbleOvalLeftEllipsis;

    protected static string|UnitEnum|null $navigationGroup = 'Lainnya';

    protected static ?string $navigationLabel = 'Konseling';

    protected static ?int $navigationSort = 5;

    protected static ?string $title = 'Konseling';

    public static function getModuleSummary(): string
    {
        return 'Layanan bimbingan dan konseling siswa.';
    }

    /**
     * @return array<int, array{title: string, description: string}>
     */
    public static function getPlannedFeatures(): array
    {
        return [];
    }
}
