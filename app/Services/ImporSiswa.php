<?php

namespace App\Services;

use App\Models\Classroom;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Impor data siswa dari berkas CSV hasil ekspor sistem lain (EMIS, Excel, dll.).
 *
 * Dikerjakan dua tahap — periksa lalu terapkan — supaya admin melihat apa
 * yang akan berubah sebelum basis data tersentuh. Siswa dicocokkan lewat
 * NISN: yang sudah ada diperbarui, yang belum ada dibuat.
 */
class ImporSiswa
{
    /** Nama kolom yang dikenali, beserta ejaan lain yang umum di berkas ekspor. */
    private const KOLOM = [
        'nisn' => ['nisn'],
        'nama' => ['nama', 'name', 'nama lengkap', 'nama siswa'],
        'kelas' => ['kelas', 'rombel', 'class'],
        'aktif' => ['aktif', 'status', 'active'],
    ];

    /**
     * Membaca dan memeriksa isi CSV tanpa menyimpan apa pun.
     *
     * @return array{baris: array<int, array<string, mixed>>, galat: array<int, string>}
     */
    public function periksa(string $isi): array
    {
        // BOM dari Excel membuat nama kolom pertama terbaca "\u{FEFF}nisn".
        $isi = preg_replace('/^\x{FEFF}/u', '', $isi) ?? $isi;
        $baris = preg_split('/\r\n|\r|\n/', trim($isi)) ?: [];

        if (count($baris) < 2) {
            return ['baris' => [], 'galat' => ['Berkas kosong atau hanya berisi judul kolom.']];
        }

        // Excel berlokal Indonesia mengekspor CSV dengan titik koma.
        $pemisah = substr_count($baris[0], ';') > substr_count($baris[0], ',') ? ';' : ',';
        $judul = array_map(fn ($j) => Str::lower(trim((string) $j)), str_getcsv(array_shift($baris), $pemisah));
        $posisi = $this->petakanKolom($judul);

        if (! isset($posisi['nisn'], $posisi['nama'])) {
            return ['baris' => [], 'galat' => ['Kolom wajib "nisn" dan "nama" tidak ditemukan pada baris judul.']];
        }

        $kelas = Classroom::query()->pluck('id', 'name')->mapWithKeys(
            fn ($id, $nama) => [Str::lower(trim($nama)) => $id],
        );
        $sudahAda = Student::query()->pluck('id', 'nisn');
        $terlihat = [];
        $hasil = [];

        foreach ($baris as $i => $mentah) {
            if (trim($mentah) === '') {
                continue;
            }

            $kolom = str_getcsv($mentah, $pemisah);
            $ambil = fn (string $k): string => trim((string) ($kolom[$posisi[$k] ?? -1] ?? ''));
            $nomor = $i + 2; // +1 judul, +1 karena manusia menghitung dari satu.

            $nisn = preg_replace('/\D/', '', $ambil('nisn')) ?? '';
            $nama = $ambil('nama');
            $namaKelas = $ambil('kelas');
            $alasan = null;

            if ($nisn === '') {
                $alasan = 'NISN kosong atau bukan angka';
            } elseif (strlen($nisn) > 20) {
                $alasan = 'NISN lebih dari 20 digit';
            } elseif ($nama === '') {
                $alasan = 'Nama kosong';
            } elseif (isset($terlihat[$nisn])) {
                $alasan = "NISN kembar dengan baris {$terlihat[$nisn]}";
            } elseif ($namaKelas !== '' && ! $kelas->has(Str::lower($namaKelas))) {
                // Tidak dibuat otomatis: salah ketik "X-A " jadi kelas baru.
                $alasan = "Kelas \"{$namaKelas}\" belum terdaftar";
            }

            $terlihat[$nisn] ??= $nomor;

            $hasil[] = [
                'nomor' => $nomor,
                'nisn' => $nisn,
                'nama' => $nama,
                'kelas' => $namaKelas,
                'classroom_id' => $namaKelas !== '' ? $kelas->get(Str::lower($namaKelas)) : null,
                'aktif' => $this->bacaAktif($ambil('aktif')),
                'aksi' => $alasan ? 'ditolak' : ($sudahAda->has($nisn) ? 'perbarui' : 'baru'),
                'alasan' => $alasan,
            ];
        }

        return ['baris' => $hasil, 'galat' => []];
    }

    /**
     * Menerapkan hasil periksa. Semua baris dalam satu transaksi: kalau satu
     * gagal di tengah jalan, tidak ada yang tersimpan setengah.
     *
     * @param  array<int, array<string, mixed>>  $baris
     * @return array{baru: int, diperbarui: int, akun: array<int, array{nisn: string, nama: string, kata_sandi: string}>}
     */
    public function terapkan(array $baris): array
    {
        $akun = [];
        $diperbarui = 0;

        DB::transaction(function () use ($baris, &$akun, &$diperbarui): void {
            foreach ($baris as $b) {
                if ($b['aksi'] === 'ditolak') {
                    continue;
                }

                $data = ['name' => $b['nama'], 'is_active' => $b['aktif']];

                if ($b['classroom_id']) {
                    $data['classroom_id'] = $b['classroom_id'];
                }

                $siswa = Student::query()->where('nisn', $b['nisn'])->first();

                if ($siswa) {
                    // Kata sandi siswa lama tidak disentuh.
                    $siswa->update($data);
                    $diperbarui++;

                    continue;
                }

                // Bukan NISN: portal belum punya fitur ganti sandi, jadi sandi
                // yang bisa ditebak akan tetap bisa ditebak selamanya.
                $sandi = Str::lower(Str::random(4)).random_int(1000, 9999);

                Student::query()->create([...$data, 'nisn' => $b['nisn'], 'password' => $sandi]);
                $akun[] = ['nisn' => $b['nisn'], 'nama' => $b['nama'], 'kata_sandi' => $sandi];
            }
        });

        return ['baru' => count($akun), 'diperbarui' => $diperbarui, 'akun' => $akun];
    }

    /** @param  array<int, array{nisn: string, nama: string, kata_sandi: string}>  $akun */
    public function csvAkun(array $akun): string
    {
        $keluaran = fopen('php://temp', 'r+');
        fputcsv($keluaran, ['nisn', 'nama', 'kata_sandi']);

        foreach ($akun as $a) {
            fputcsv($keluaran, [$a['nisn'], $a['nama'], $a['kata_sandi']]);
        }

        rewind($keluaran);

        return (string) stream_get_contents($keluaran);
    }

    /**
     * @param  array<int, string>  $judul
     * @return array<string, int>
     */
    private function petakanKolom(array $judul): array
    {
        $posisi = [];

        foreach (self::KOLOM as $kunci => $ejaan) {
            foreach ($judul as $i => $j) {
                if (in_array($j, $ejaan, true)) {
                    $posisi[$kunci] = $i;
                    break;
                }
            }
        }

        return $posisi;
    }

    /** Kolom aktif boleh kosong (berarti aktif) atau ditulis dengan berbagai cara. */
    private function bacaAktif(string $nilai): bool
    {
        return ! in_array(Str::lower($nilai), ['0', 'tidak', 'nonaktif', 'false', 'no', 'keluar', 'lulus'], true);
    }
}
