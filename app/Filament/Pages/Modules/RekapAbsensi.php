<?php

namespace App\Filament\Pages\Modules;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class RekapAbsensi extends ModulePage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedTableCells;

    protected static string|UnitEnum|null $navigationGroup = 'Absensi';

    protected static ?string $navigationLabel = 'Rekap Absensi Bulanan';

    protected static ?int $navigationSort = 4;

    protected static ?string $title = 'Rekap Absensi Bulanan';

    public static function getModuleSummary(): string
    {
        return 'Matriks kehadiran siswa per bulan dalam format buku induk.';
    }

    /**
     * @return array<int, array{title: string, description: string}>
     */
    public static function getPlannedFeatures(): array
    {
        return [
            [
                'title' => 'Kode Kehadiran',
                'description' => 'H hadir, T terlambat, S sakit, I izin, A alpha, L libur.',
            ],
            [
                'title' => 'Menu Terkait',
                'description' => 'Set hari libur, izin siswa global, absen manual, edit kehadiran, jurnal guru inval, piket tatib, dan absensi ibadah.',
            ],
        ];
    }
}
