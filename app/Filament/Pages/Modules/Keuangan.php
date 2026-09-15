<?php

namespace App\Filament\Pages\Modules;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class Keuangan extends ModulePage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedBanknotes;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Keuangan';

    protected static ?int $navigationSort = 1;

    protected static ?string $title = 'Keuangan';

    public static function getModuleSummary(): string
    {
        return 'Kasir, tagihan, dan laporan pembayaran siswa.';
    }

    /**
     * @return array<int, array{title: string, description: string}>
     */
    public static function getPlannedFeatures(): array
    {
        return [
            [
                'title' => 'Layanan Kasir',
                'description' => 'Transaksi pembayaran real-time dengan metode QRIS, tunai, atau transfer, plus ekspor dan cetak laporan.',
            ],
            [
                'title' => 'Master Pembayaran',
                'description' => 'Jenis tagihan dengan tipe wajib atau bebas, grup SPP, Infaq, Mahad, dan nominal dasarnya.',
            ],
            [
                'title' => 'Penerbitan Tagihan Massal',
                'description' => 'Terbitkan tagihan ke banyak kelas atau siswa terpilih sekaligus.',
            ],
            [
                'title' => 'Penyesuaian & Laporan',
                'description' => 'Sesuaikan nominal per siswa, lalu susun laporan dan pembatalan transaksi.',
            ],
        ];
    }
}
