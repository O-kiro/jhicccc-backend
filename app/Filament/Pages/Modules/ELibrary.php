<?php

namespace App\Filament\Pages\Modules;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class ELibrary extends ModulePage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBookmarkSquare;

    protected static string|UnitEnum|null $navigationGroup = 'E-Library';

    protected static ?string $navigationLabel = 'Sirkulasi Buku';

    protected static ?int $navigationSort = 3;

    protected static ?string $title = 'E-Library';

    public static function getModuleSummary(): string
    {
        return 'Sirkulasi perpustakaan berbasis kartu RFID. Katalog dan peminjaman dasar sudah bisa dikelola lewat menu Buku dan Pinjaman Buku.';
    }

    /**
     * @return array<int, array{title: string, description: string}>
     */
    public static function getPlannedFeatures(): array
    {
        return [
            [
                'title' => 'Sirkulasi Buku',
                'description' => 'Tap kartu siswa, pilih buku dan durasi, lalu proses peminjaman.',
            ],
            [
                'title' => 'Katalog & Laporan',
                'description' => 'Master setting, katalog buku, daftar sedang dipinjam, dan riwayat pengembalian.',
            ],
        ];
    }
}
