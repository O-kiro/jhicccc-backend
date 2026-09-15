<?php

namespace App\Filament\Pages\Modules;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class JurnalKbm extends ModulePage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookOpen;

    protected static string|UnitEnum|null $navigationGroup = 'Absensi';

    protected static ?string $navigationLabel = 'Monitoring Jurnal KBM';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'Monitoring Jurnal KBM';

    public static function getModuleSummary(): string
    {
        return 'Pantau keterisian jurnal mengajar guru per jadwal harian.';
    }

    /**
     * @return array<int, array{title: string, description: string}>
     */
    public static function getPlannedFeatures(): array
    {
        return [
            [
                'title' => 'Matriks Jam Pelajaran',
                'description' => 'Kelas dikali jam ke-1 sampai ke-8; tiap sel memuat mata pelajaran, nama guru, dan status Terekam / Kosong / Tidak Ada Jadwal.',
            ],
            [
                'title' => 'Cetak Harian',
                'description' => 'Cetak rekap keterisian jurnal untuk tanggal terpilih.',
            ],
        ];
    }
}
