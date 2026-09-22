<?php

namespace App\Support;

/** Nama hari menurut nomor ISO (Senin = 1), sama dengan schedules.day_of_week. */
final class Hari
{
    public const NAMA = [
        1 => 'Senin',
        2 => 'Selasa',
        3 => 'Rabu',
        4 => 'Kamis',
        5 => 'Jumat',
        6 => 'Sabtu',
        7 => 'Minggu',
    ];

    public static function nama(int $iso): string
    {
        return self::NAMA[$iso] ?? '—';
    }
}
