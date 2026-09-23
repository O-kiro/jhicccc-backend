<?php

namespace Database\Seeders;

use App\Models\AlumniAccount;
use App\Models\AlumniForumCategory;
use App\Models\AlumniForumReply;
use App\Models\AlumniForumThread;
use App\Models\AlumniOutcome;
use App\Models\Scholarship;
use Illuminate\Database\Seeder;

/**
 * Isi awal Portal Alumni, diambil dari alumni.md (desain Figma).
 *
 * Idempoten: seluruhnya updateOrCreate dengan kunci alami, jadi menjalankan
 * ulang tidak menggandakan apa pun.
 */
class AlumniSeeder extends Seeder
{
    public function run(): void
    {
        $akun = $this->akun();
        $this->beasiswa();
        $this->sebaran();
        $this->forum($akun);
    }

    /**
     * @return array<string, AlumniAccount>
     */
    private function akun(): array
    {
        $rows = [
            ['aldian', 'Aldian Dwi Nanda', 'aldian@alumni.mankotabatu.sch.id', 2019, 'Mahasiswa Teknik Informatika UB'],
            ['fajar', 'Fajar Ramadhan', 'fajar@alumni.mankotabatu.sch.id', 2019, 'Management Trainee di BUMN'],
            ['rizky', 'Rizky Zakaria', 'rizky@alumni.mankotabatu.sch.id', 2019, 'Mahasiswa Kedokteran UNAIR'],
            ['budi', 'Budi Santoso', 'budi@alumni.mankotabatu.sch.id', 2023, 'Wirausaha & Freelancer'],
            ['nabila', 'Nabila Azzahra', 'nabila@alumni.mankotabatu.sch.id', 2020, 'Mahasiswa Hubungan Internasional UI'],
            ['arif', 'Arif Maulana', 'arif@alumni.mankotabatu.sch.id', 2021, 'Taruna Sekolah Kedinasan'],
        ];

        $akun = [];

        foreach ($rows as [$kunci, $nama, $email, $tahun, $kegiatan]) {
            $akun[$kunci] = AlumniAccount::query()->updateOrCreate(
                ['email' => $email],
                [
                    'name' => $nama,
                    'graduation_year' => $tahun,
                    'occupation' => $kegiatan,
                    'is_active' => true,
                    // Sandi contoh, sama dengan akun demo lain.
                    'password' => 'password',
                ],
            );
        }

        return $akun;
    }

    private function beasiswa(): void
    {
        $rows = [
            ['Sains & Teknologi', 'Beasiswa Riset Peneliti Muda MAN Kota Batu', 25,
                'Dana Riset Rp 5.000.000 + Lab Terpadu', 'Siswa Kelas X & XI bidang KIR',
                '2026-11-15', 'dibuka', 96, 24],
            ['Olimpiade Sains', 'Beasiswa Prestasi Juara OSN & KSM', 15,
                'Bebas SPP Penuh 1 Tahun + Bimbingan Khusus', 'Siswa Juara Tingkat Kota/Provinsi/Nasional',
                '2026-11-30', 'dibuka', 54, 14],
            ['Keagamaan & Tahfidz', 'Beasiswa Tahfidz Al-Qur\'an 30 Juz', 20,
                'Subsidi Asrama Penuh + Sertifikasi Kemenag', 'Santri & Siswa Tahfidz Terverifikasi',
                '2026-12-10', 'segera_ditutup', 72, 18],
            ['Bantuan Pendidikan', 'Beasiswa Indonesia Pintar & Afirmasi Madrasah', 50,
                'Bantuan Biaya Perlengkapan & Living Cost', 'Siswa penerima KIP & afirmasi madrasah',
                '2026-12-20', 'segera_ditutup', 120, 46],
            ['Teknologi Digital', 'Beasiswa Talenta Riset Robotika & AI Madrasah', 0,
                'Kit IoT/Robotika + Akses Inkubasi Lomba Nasional', 'Siswa Ekstrakurikuler Robotika & IT',
                '2027-01-05', 'ditutup', 0, 0],
            ['Seni & Humaniora', 'Beasiswa Prestasi Duta Bahasa & Seni Budaya', 0,
                'Biaya Pembinaan Minat Bakat + Sertifikat Prestasi', 'Siswa Berprestasi FLS2N / Debat Bahasa',
                '2027-01-15', 'ditutup', 0, 0],
        ];

        foreach ($rows as $i => [$kategori, $nama, $kuota, $fasilitas, $sasaran, $batas, $status, $pendaftar, $lolos]) {
            Scholarship::query()->updateOrCreate(
                ['name' => $nama],
                [
                    'category' => $kategori,
                    'quota' => $kuota,
                    'benefits' => $fasilitas,
                    'target' => $sasaran,
                    'deadline' => $batas,
                    'status' => $status,
                    'applicants' => $pendaftar,
                    'verified' => $lolos,
                    'sort' => $i,
                ],
            );
        }
    }

    private function sebaran(): void
    {
        $rows = [
            [2026, 'ptn', 192, 'SNBP, SNBT, & Seleksi Mandiri PTN', 0],
            [2026, 'pts', 58, 'Jalur Rapor & Beasiswa Mitra Kampus', 1],
            [2026, 'kedinasan', 48, 'SKD & Tes Kesemaptaan Ikatan Dinas', 2],
            [2026, 'kerja', 22, 'Mitra Industri, BUMN, & Usaha Mandiri', 3],
            // Tahun sebelumnya supaya penyaring tahun ada isinya.
            [2025, 'ptn', 168, 'SNBP & SNBT', 0],
            [2025, 'pts', 64, 'Jalur Rapor & Beasiswa Mitra Kampus', 1],
            [2025, 'kedinasan', 39, 'SKD Ikatan Dinas', 2],
            [2025, 'kerja', 31, 'Mitra Industri & Usaha Mandiri', 3],
        ];

        foreach ($rows as [$tahun, $kategori, $siswa, $catatan, $urut]) {
            AlumniOutcome::query()->updateOrCreate(
                ['year' => $tahun, 'category' => $kategori],
                ['students' => $siswa, 'note' => $catatan, 'sort' => $urut],
            );
        }
    }

    /**
     * @param  array<string, AlumniAccount>  $akun
     */
    private function forum(array $akun): void
    {
        $kategori = [];

        $rows = [
            ['Karir & Pekerjaan', 'Info lowongan kerja, magang, tips interview, dan berbagi pengalaman dunia kerja.', 'users', 'blue', 0],
            ['Dunia Perkuliahan', 'Info beasiswa, jalur masuk kampus, tips kehidupan kampus, dan review jurusan.', 'book', 'teal', 1],
            ['Life Update', 'Diskusi proyek, usaha/bisnis alumni, kolaborasi, dan agenda reuni alumni.', 'sparkle', 'gold', 2],
        ];

        foreach ($rows as [$nama, $keterangan, $ikon, $warna, $urut]) {
            $kategori[$nama] = AlumniForumCategory::query()->updateOrCreate(
                ['name' => $nama],
                ['description' => $keterangan, 'icon' => $ikon, 'tone' => $warna, 'sort' => $urut],
            );
        }

        $topik = [
            [
                'Karir & Pekerjaan', 'fajar',
                'Tips Lolos Management Trainee (MT) untuk Fresh Graduate',
                'Banyak yang tanya bagaimana caranya bisa tembus seleksi MT di perusahaan BUMN atau multinasional. Saya bagi beberapa poin penting dari pengalaman saya: persiapkan psikotes jauh hari, kuasai studi kasus sederhana, dan latih wawancara dalam bahasa Inggris.',
                82,
                ['Terima kasih kak, sangat membantu!', 'Boleh minta rekomendasi buku psikotesnya?'],
            ],
            [
                'Dunia Perkuliahan', 'rizky',
                'Tips Lolos UTBK-SNBT dan Beasiswa KIP-K di Perguruan Tinggi Negeri',
                'Halo adik-adik dan rekan alumni! Mau berbagi strategi belajar, referensi buku, dan berkas yang perlu disiapkan untuk pendaftaran KIP-K berdasarkan pengalaman kemarin. Siapkan berkas dari jauh hari supaya tidak kelabakan saat pengumuman.',
                64,
                ['Berkasnya discan warna atau hitam putih kak?', 'Mantap, ditunggu sesi sharing berikutnya.'],
            ],
            [
                'Life Update', 'budi',
                'Rencana Reuni Akbar & Temu Kangen Lintas Angkatan',
                'Halo kawan-kawan alumni MAN Kota Batu, ada usulan mengadakan gathering dan sharing session karir di kampus atau kota masing-masing. Ada yang tertarik membantu kepanitiaan? Rencananya digelar setelah semester ganjil berakhir.',
                41,
                ['Ikut bantu kalau di Malang!'],
            ],
        ];

        foreach ($topik as [$namaKategori, $penulis, $judul, $isi, $suka, $balasan]) {
            $thread = AlumniForumThread::query()->updateOrCreate(
                ['title' => $judul],
                [
                    'alumni_forum_category_id' => $kategori[$namaKategori]->id,
                    'alumni_account_id' => $akun[$penulis]->id,
                    'body' => $isi,
                    'like_count' => $suka,
                ],
            );

            foreach ($balasan as $i => $teks) {
                AlumniForumReply::query()->updateOrCreate(
                    ['alumni_forum_thread_id' => $thread->id, 'body' => $teks],
                    [
                        'alumni_account_id' => $akun[$i === 0 ? 'nabila' : 'arif']->id,
                        'parent_id' => null,
                    ],
                );
            }
        }
    }
}
