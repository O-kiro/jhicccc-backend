<?php

namespace App\Filament\Pages\Modules;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class Humas extends ModulePage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedUsers;

    protected static string|UnitEnum|null $navigationGroup = 'Humas';

    protected static ?string $navigationLabel = 'Rekap Buku Tamu';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Humas';

    public static function getModuleSummary(): string
    {
        return 'Buku tamu dan katalog layanan madrasah.';
    }

    /**
     * @return array<int, array{title: string, description: string}>
     */
    public static function getPlannedFeatures(): array
    {
        return [
            [
                'title' => 'Rekap Buku Tamu',
                'description' => 'Log kunjungan dengan kode registrasi, instansi, jam, status PENDING atau KUNJUNGAN, serta penilaian layanan.',
            ],
            [
                'title' => 'Katalog Master Layanan',
                'description' => 'Kelola jenis layanan dan kunjungan beserta target pemohon, deskripsi, dan syarat ketentuan.',
            ],
        ];
    }
}
