<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Memperbarui satu butir fitur Portal Siswa.
 *
 * Butirnya menyebut "E-Learning & CBT" — dua nama layanan yang sudah tidak
 * ada sejak keduanya diganti Portal Guru dan Portal Alumni. Satu-satunya
 * sisa penyebutan nama lama di situs.
 *
 * Mengganti satu string di dalam kolom JSON, bukan menimpa seluruh barisnya,
 * supaya penyuntingan admin pada butir lain tidak ikut hilang.
 */
return new class extends Migration
{
    private const LAMA = 'Terintegrasi dengan E-Learning & CBT';

    private const BARU = 'Terintegrasi dengan Portal Guru & Perpustakaan Digital';

    public function up(): void
    {
        $this->ganti(self::LAMA, self::BARU);
    }

    public function down(): void
    {
        $this->ganti(self::BARU, self::LAMA);
    }

    private function ganti(string $dari, string $ke): void
    {
        $baris = DB::table('digital_services')->whereNotNull('features')->get(['id', 'features']);

        foreach ($baris as $b) {
            $fitur = json_decode((string) $b->features, true);

            if (! is_array($fitur)) {
                continue;
            }

            $berubah = false;

            foreach ($fitur as $i => $f) {
                if ($f === $dari) {
                    $fitur[$i] = $ke;
                    $berubah = true;
                }
            }

            if ($berubah) {
                DB::table('digital_services')
                    ->where('id', $b->id)
                    ->update(['features' => json_encode($fitur, JSON_UNESCAPED_UNICODE)]);
            }
        }
    }
};
