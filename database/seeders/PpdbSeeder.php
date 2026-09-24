<?php

namespace Database\Seeders;

use App\Models\PpdbRegistrant;
use Illuminate\Database\Seeder;

/**
 * Dua akun pendaftar contoh supaya alur unggah berkas bisa dicoba tanpa
 * membuat data lebih dulu. Idempoten: dikunci nomor pendaftarannya.
 */
class PpdbSeeder extends Seeder
{
    public function run(): void
    {
        $rows = [
            ['PPDB26-0001', 'Nayla Putri Ramadhani', 'Prestasi', 'SMPN 1 Kota Batu', '081234567801'],
            ['PPDB26-0002', 'Raka Dwi Saputra', 'Reguler 1', 'MTsN 2 Kota Batu', '081234567802'],
        ];

        foreach ($rows as [$nomor, $nama, $jalur, $asal, $hp]) {
            PpdbRegistrant::query()->updateOrCreate(
                ['registration_number' => $nomor],
                [
                    'name' => $nama,
                    'jalur' => $jalur,
                    'origin_school' => $asal,
                    'phone' => $hp,
                    'is_active' => true,
                    // Sandi contoh, sama dengan akun demo lain.
                    'password' => 'password',
                ],
            );
        }
    }
}
