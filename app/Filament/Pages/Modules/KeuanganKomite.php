<?php

namespace App\Filament\Pages\Modules;

use BackedEnum;
use Filament\Support\Icons\Heroicon;
use UnitEnum;

class KeuanganKomite extends ModulePage
{
    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedWallet;

    protected static string|UnitEnum|null $navigationGroup = 'Keuangan';

    protected static ?string $navigationLabel = 'Keuangan Komite';

    protected static ?int $navigationSort = 2;

    protected static ?string $title = 'Keuangan Komite';

    public static function getModuleSummary(): string
    {
        return 'Pembukuan pemasukan dan pengeluaran dana komite.';
    }

    /**
     * @return array<int, array{title: string, description: string}>
     */
    public static function getPlannedFeatures(): array
    {
        return [
            [
                'title' => 'Buku Kas Umum',
                'description' => 'Total pemasukan, pengeluaran, dan saldo kas berjalan dengan bukti transaksi.',
            ],
            [
                'title' => 'Kategori Keuangan',
                'description' => 'Kelola kategori pemasukan seperti dana BOS dan iuran komite, serta pengeluaran seperti gaji dan biaya operasional.',
            ],
        ];
    }
}
