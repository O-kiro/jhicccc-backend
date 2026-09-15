<?php

namespace App\Filament\Pages\Modules;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class Sarpras extends ModulePage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBuildingOffice2;

    protected static string|UnitEnum|null $navigationGroup = 'Sarana & Prasarana';

    protected static ?string $navigationLabel = 'Master Sarpras & Aset';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Sarana & Prasarana';

    public static function getModuleSummary(): string
    {
        return 'Pangkalan data ruangan dan inventaris aset madrasah.';
    }

    /**
     * @return array<int, array{title: string, description: string}>
     */
    public static function getPlannedFeatures(): array
    {
        return [
            [
                'title' => 'Master Sarpras',
                'description' => 'Daftar lokasi, ruangan, penanggung jawab, spesifikasi, dan kondisinya.',
            ],
            [
                'title' => 'Buku Induk Barang',
                'description' => 'Inventaris aset umum dan inventaris kelas lengkap dengan foto, pengadaan, harga, dan kondisi.',
            ],
            [
                'title' => 'Peminjaman & Booking',
                'description' => 'Peminjaman barang, reservasi ruangan, serta pencatatan perbaikan dan pemutihan aset.',
            ],
        ];
    }
}
