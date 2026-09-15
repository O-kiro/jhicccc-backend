<?php

namespace App\Filament\Pages\Modules;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class Kesiswaan extends ModulePage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedIdentification;

    protected static string|UnitEnum|null $navigationGroup = 'Kesiswaan';

    protected static ?string $navigationLabel = 'Klasemen Poin Kedisiplinan';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Kesiswaan';

    public static function getModuleSummary(): string
    {
        return 'Monitoring kedisiplinan siswa dan rekap poin tata tertib.';
    }

    /**
     * @return array<int, array{title: string, description: string}>
     */
    public static function getPlannedFeatures(): array
    {
        return [
            [
                'title' => 'Klasemen Poin Kedisiplinan',
                'description' => 'Peringkat siswa dengan poin pelanggaran tertinggi, lengkap dengan jumlah kasus, poin penghargaan, dan penerbitan surat pernyataan atau peringatan.',
            ],
            [
                'title' => 'Filter & Ekspor',
                'description' => 'Saring per jenjang, kelas, dan rentang tanggal; hasilnya dapat diunduh sebagai Excel.',
            ],
        ];
    }
}
