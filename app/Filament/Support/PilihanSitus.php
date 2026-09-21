<?php

namespace App\Filament\Support;

use Filament\Forms\Components\Select;

/**
 * Pilihan bersama untuk formulir konten situs (menu My Website).
 *
 * Ikon harus salah satu yang dikenal frontend — daftar ini disalin dari tipe
 * IconName di lib/content.ts. Ikon di luar daftar tidak akan tergambar sama
 * sekali di situs, jadi formulir tidak menerima teks bebas.
 */
final class PilihanSitus
{
    public const IKON = [
        'ppdb', 'rdm', 'cbt', 'elearning', 'library', 'attendance', 'ppid', 'ebook', 'research',
        'olympiad', 'tahfidz', 'trophy', 'calendar', 'sparkle', 'search', 'pin', 'phone', 'mail',
        'clock', 'quote', 'star8', 'shield', 'users', 'book', 'globe', 'heart', 'play', 'check',
        'flask', 'palette', 'ball', 'mic', 'leaf', 'camera', 'flame', 'bell', 'download', 'chat',
        'help', 'flag', 'chart', 'wifi', 'grid', 'bookmark', 'sigma',
    ];

    public const WARNA = ['teal' => 'Teal', 'blue' => 'Biru', 'gold' => 'Emas'];

    public static function ikon(string $nama = 'icon'): Select
    {
        return Select::make($nama)
            ->label('Ikon')
            ->options(array_combine(self::IKON, self::IKON))
            ->searchable()
            ->required();
    }

    public static function warna(string $nama = 'tone'): Select
    {
        return Select::make($nama)
            ->label('Warna')
            ->options(self::WARNA)
            ->default('teal')
            ->required();
    }
}
