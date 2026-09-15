<?php

namespace App\Filament\Pages\Modules;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class LiveMonitoring extends ModulePage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedSignal;

    protected static string|UnitEnum|null $navigationGroup = 'Absensi';

    protected static ?string $navigationLabel = 'Live Monitoring';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Live Monitoring';

    public static function getModuleSummary(): string
    {
        return 'Pantau status kehadiran real-time dan beri izin manual.';
    }

    /**
     * @return array<int, array{title: string, description: string}>
     */
    public static function getPlannedFeatures(): array
    {
        return [
            [
                'title' => 'Ringkasan Kehadiran',
                'description' => 'Total siswa, hadir, telat, izin, sakit, dan alpha pada hari berjalan.',
            ],
            [
                'title' => 'Sumber Absen',
                'description' => 'Menandai apakah kehadiran datang dari mesin fingerprint, wali kelas, atau wali murid.',
            ],
        ];
    }
}
