<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Mengganti tiga layanan lama dengan portal per peran.
 *
 *   RDM        → Siswa
 *   CBT        → Guru
 *   E-Learning → Alumni
 *
 * Dikerjakan lewat migrasi, bukan seeder, karena SitusSeeder mencocokkan
 * baris lewat `name`. Menjalankannya ulang di basis data yang sudah terisi
 * akan menambah tiga baris baru tanpa membuang yang lama — beranda lalu
 * menampilkan sembilan kartu.
 *
 * Isinya diambil dari database/seeders/data/situs.json supaya pemasangan baru
 * (lewat seeder) dan pemasangan lama (lewat migrasi ini) menghasilkan hal yang
 * sama persis.
 */
return new class extends Migration
{
    /** Nama lama => nama baru. */
    private const PETA = [
        'RDM' => 'Siswa',
        'CBT' => 'Guru',
        'E-Learning' => 'Alumni',
    ];

    public function up(): void
    {
        $benih = $this->benih();

        foreach (self::PETA as $lama => $baru) {
            $isi = $benih[$baru] ?? null;

            // Benihnya hilang atau berubah nama: jangan menebak, biarkan saja.
            if (! $isi) {
                continue;
            }

            // Baris lama sudah tidak ada (mis. dihapus admin) — tidak perlu
            // membuatnya, seeder yang mengurus pemasangan baru.
            if (! DB::table('digital_services')->where('name', $lama)->exists()) {
                continue;
            }

            DB::table('digital_services')->where('name', $lama)->update($isi);
        }

        $this->rapikanUrutan();
    }

    public function down(): void
    {
        // Hanya mengembalikan namanya. Isi lama tidak disimpan di mana pun,
        // dan seeder akan menulisinya lagi bila dijalankan.
        foreach (self::PETA as $lama => $baru) {
            DB::table('digital_services')->where('name', $baru)->update(['name' => $lama]);
        }
    }

    /** Isi tiga layanan baru, dibaca dari berkas benih. */
    private function benih(): array
    {
        $berkas = database_path('seeders/data/situs.json');

        if (! is_file($berkas)) {
            return [];
        }

        $data = json_decode((string) file_get_contents($berkas), true);
        $keluaran = [];

        foreach ($data['digitalServices'] ?? [] as $s) {
            if (! in_array($s['name'], self::PETA, true)) {
                continue;
            }

            $d = $s['detail'] ?? [];

            $keluaran[$s['name']] = [
                'name' => $s['name'],
                'slug' => $d['slug'] ?? null,
                'description' => $s['desc'],
                'href' => $s['href'],
                'icon' => $s['icon'],
                'tone' => $s['tone'],
                'login_href' => $s['login']['href'] ?? null,
                'login_label' => $s['login']['label'] ?? null,
                'login_note' => $d['loginNote'] ?? null,
                'guide' => isset($d['guide']) ? json_encode($d['guide'], JSON_UNESCAPED_UNICODE) : null,
                'full_name' => $d['fullName'] ?? null,
                'audience' => $d['audience'] ?? null,
                'intro' => $d['intro'] ?? null,
                'about' => isset($d['about']) ? json_encode($d['about'], JSON_UNESCAPED_UNICODE) : null,
                'highlights' => isset($d['highlights']) ? json_encode($d['highlights'], JSON_UNESCAPED_UNICODE) : null,
                'features_title' => $d['featuresTitle'] ?? null,
                'features' => isset($d['features']) ? json_encode($d['features'], JSON_UNESCAPED_UNICODE) : null,
                'steps' => isset($d['steps']) ? json_encode($d['steps'], JSON_UNESCAPED_UNICODE) : null,
                'note' => $d['note'] ?? null,
                'help' => isset($d['help']) ? json_encode($d['help'], JSON_UNESCAPED_UNICODE) : null,
                'updated_at' => now(),
            ];
        }

        return $keluaran;
    }

    /**
     * Menyamakan urutan dengan berkas benih.
     *
     * Bukan sekadar kerapian: tata letak bento di beranda melebarkan kartu
     * pertama dan terakhir, jadi urutan yang bergeser mengubah tampilannya.
     */
    private function rapikanUrutan(): void
    {
        $berkas = database_path('seeders/data/situs.json');

        if (! is_file($berkas)) {
            return;
        }

        $data = json_decode((string) file_get_contents($berkas), true);

        foreach ($data['digitalServices'] ?? [] as $i => $s) {
            DB::table('digital_services')->where('name', $s['name'])->update(['sort' => $i]);
        }
    }
};
