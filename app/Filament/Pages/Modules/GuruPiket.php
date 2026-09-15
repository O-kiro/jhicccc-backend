<?php

namespace App\Filament\Pages\Modules;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class GuruPiket extends ModulePage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedClipboardDocumentList;

    protected static string|UnitEnum|null $navigationGroup = 'Absensi';

    protected static ?string $navigationLabel = 'Guru Piket';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Guru Piket';

    public static function getModuleSummary(): string
    {
        return 'Log izin keluar dan masuk siswa secara langsung.';
    }

    /**
     * @return array<int, array{title: string, description: string}>
     */
    public static function getPlannedFeatures(): array
    {
        return [
            [
                'title' => 'Izin Keluar/Masuk',
                'description' => 'Catatan siswa yang izin keluar kelas, masuk kelas, atau keluar lingkungan sekolah beserta jam dan keperluannya.',
            ],
        ];
    }
}
