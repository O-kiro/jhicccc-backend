<?php

namespace App\Filament\Pages\Modules;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class MyWebsite extends ModulePage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedGlobeAlt;

    protected static string|UnitEnum|null $navigationGroup = 'Konten';

    protected static ?string $navigationLabel = 'My Website';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'My Website';

    public static function getModuleSummary(): string
    {
        return 'CMS untuk situs publik madrasah.';
    }

    /**
     * @return array<int, array{title: string, description: string}>
     */
    public static function getPlannedFeatures(): array
    {
        return [
            [
                'title' => 'Tampilan & Pop-up',
                'description' => 'Pengaturan tampilan beranda dan pop-up informasi.',
            ],
            [
                'title' => 'Layanan Cepat',
                'description' => 'Maksimal delapan menu akses cepat di beranda, dengan pratinjau langsung.',
            ],
            [
                'title' => 'Konten Situs',
                'description' => 'Berita, agenda, program unggulan, prestasi, ekstrakurikuler, fasilitas, galeri, QnA, testimoni, dan alumni.',
            ],
        ];
    }
}
