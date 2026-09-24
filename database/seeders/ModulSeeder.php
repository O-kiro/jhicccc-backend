<?php

namespace Database\Seeders;

use App\Models\Book;
use App\Models\BookLoan;
use App\Models\Classroom;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\DisciplineRule;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamQuestion;
use App\Models\ExamQuestionOption;
use App\Models\ExamResult;
use App\Models\ForumCategory;
use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\ModuleCompletion;
use App\Models\Quote;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Mengisi modul Kursus, Ujian/CBT, Perpustakaan, dan Forum dengan data dari
 * design-siswa.md — pasangan PortalSeeder, yang mengisi data inti.
 *
 * Dijalankan setelah PortalSeeder karena bergantung pada siswa, kelas, guru,
 * dan mata pelajaran yang dibuat di sana.
 */
class ModulSeeder extends Seeder
{
    public function run(): void
    {
        $siswa = Student::query()->first();
        $kelas = Classroom::query()->first();

        if (! $siswa || ! $kelas) {
            $this->command?->warn('ModulSeeder dilewati: jalankan PortalSeeder lebih dulu.');

            return;
        }

        $this->kutipan();
        $this->bukuTatib();
        $this->kursus($siswa, $kelas);
        $this->ujian($siswa, $kelas);
        $this->perpustakaan($siswa);
        $this->forum($siswa);
    }

    /**
     * Buku tatib: daftar pelanggaran dan penghargaan beserta bobot poinnya.
     *
     * Dipakai modul Kesiswaan di panel admin dan menu Lapor Tatib di portal
     * guru — tanpa isi ini, guru tidak punya apa pun untuk dipilih.
     */
    private function bukuTatib(): void
    {
        $rows = [
            ['TL-01', 'Terlambat masuk madrasah', 'pelanggaran', 'Kedisiplinan', 5],
            ['TL-02', 'Membolos jam pelajaran', 'pelanggaran', 'Kedisiplinan', 25],
            ['SR-01', 'Seragam tidak sesuai ketentuan', 'pelanggaran', 'Kerapian', 10],
            ['HP-01', 'Menggunakan ponsel saat KBM tanpa izin', 'pelanggaran', 'Kedisiplinan', 15],
            ['KT-01', 'Mengganggu ketertiban kelas', 'pelanggaran', 'Ketertiban', 15],
            ['ET-01', 'Tidak sopan kepada guru atau tendik', 'pelanggaran', 'Akhlak', 50],
            ['PR-01', 'Juara lomba tingkat kota', 'penghargaan', 'Prestasi', 25],
            ['PR-02', 'Juara lomba tingkat provinsi atau nasional', 'penghargaan', 'Prestasi', 50],
            ['PR-03', 'Membantu kegiatan madrasah secara aktif', 'penghargaan', 'Kepedulian', 10],
        ];

        foreach ($rows as [$kode, $judul, $jenis, $kategori, $poin]) {
            DisciplineRule::query()->updateOrCreate(
                ['code' => $kode],
                [
                    'title' => $judul,
                    'kind' => $jenis,
                    'category' => $kategori,
                    'points' => $poin,
                    'is_active' => true,
                ],
            );
        }
    }

    /** Kutipan sapaan di halaman Overview; berganti tiap hari. */
    private function kutipan(): void
    {
        $rows = [
            ['Barang siapa menempuh jalan untuk mencari ilmu, Allah akan mudahkan baginya jalan menuju surga.', 'HR. Muslim'],
            ['Menuntut ilmu itu wajib atas setiap muslim.', 'HR. Ibnu Majah'],
            ['Sebaik-baik manusia adalah yang paling bermanfaat bagi manusia lain.', 'HR. Ahmad'],
            ['Allah akan meninggikan derajat orang-orang yang beriman dan berilmu di antara kamu.', 'QS. Al-Mujadalah: 11'],
            ['Barang siapa bersungguh-sungguh, pasti akan berhasil.', 'Pepatah Arab'],
        ];

        foreach ($rows as [$isi, $sumber]) {
            Quote::query()->updateOrCreate(['body' => $isi], ['source' => $sumber, 'is_active' => true]);
        }
    }

    /** design-siswa.md §5 — enam mata pelajaran beserta progres modulnya. */
    private function kursus(Student $siswa, Classroom $kelas): void
    {
        // Nama gurunya sengaja sama dengan yang mengajar mapel itu di tabel
        // jadwal (lihat PortalSeeder::createSchedule). Kalau berbeda, satu
        // mapel muncul di portal dua guru sekaligus dan membingungkan.
        $rows = [
            ['quran-hadist', 'Ustadz Ahmad Fauzi, M.Ag', 12, 72],
            ['fiqih', 'Ust. H. Abdurrahman', 10, 64],
            ['matematika', 'Rini Waraswati, S.Pd, M.Si', 14, 58],
            ['kimia', 'Dra. Sukrawati Arni', 11, 45],
            ['bahasa-inggris', 'Indah Rahmayanti, S.Pd', 9, 81],
            ['fisika', 'Anisak Intan Eka Prani, M.Si', 13, 53],
        ];

        foreach ($rows as [$slug, $namaGuru, $modul, $progres]) {
            $mapel = Subject::query()->where('slug', $slug)->first();
            $guru = Teacher::query()->firstOrCreate(['name' => $namaGuru]);

            if (! $mapel) {
                continue;
            }

            $course = Course::query()->updateOrCreate(
                [
                    'subject_id' => $mapel->id,
                    'classroom_id' => $kelas->id,
                    'academic_year' => '2025/2026',
                    'semester' => 'Ganjil',
                ],
                ['teacher_id' => $guru->id],
            );

            Enrollment::query()->firstOrCreate(
                ['student_id' => $siswa->id, 'course_id' => $course->id],
            );

            // Jumlah modul menentukan angka di kartu Kursus — portal
            // menghitungnya langsung dari relasi ini.
            for ($n = 1; $n <= $modul; $n++) {
                CourseModule::query()->updateOrCreate(
                    ['course_id' => $course->id, 'number' => $n],
                    [
                        'title' => "Modul $n — {$mapel->name}",
                        'description' => "Materi pertemuan ke-$n. Judul dan tautan sesungguhnya diisi guru lewat panel admin.",
                        // Sengaja kosong: menautkan Drive milik orang lain
                        // hanya akan jadi tautan mati saat dipresentasikan.
                        'url' => null,
                    ],
                );
            }

            // Progres contoh dari design-siswa.md (72%, 64%, …) diwujudkan
            // sebagai modul-modul pertama yang ditandai selesai — progres kini
            // dihitung dari modul, bukan diketik.
            $selesai = (int) round($progres * $modul / 100);
            foreach ($course->modules()->orderBy('number')->limit($selesai)->pluck('id') as $idModul) {
                ModuleCompletion::query()->firstOrCreate(
                    ['student_id' => $siswa->id, 'course_module_id' => $idModul],
                    ['completed_at' => now()],
                );
            }
        }
    }

    /** design-siswa.md §3 dan §4 — ujian mendatang, hasil, dan soal CBT. */
    private function ujian(Student $siswa, Classroom $kelas): void
    {
        $mapel = fn (string $slug) => Subject::query()->where('slug', $slug)->first();

        // Waktu dibuat relatif terhadap sekarang agar countdown di portal
        // selalu masuk akal, tidak peduli kapan seeder dijalankan.
        $mendatang = [
            ['matematika', 'Calculus & Integration', 'Tinggi', now()->addHours(2)->addMinutes(14), 120],
            ['sejarah-kebudayaan-islam', 'Masa Kejayaan An-Dalusia', null, now()->addHours(6), 90],
            ['bahasa-inggris', 'Reading & Vocab', null, now()->addDay(), 90],
        ];

        foreach ($mendatang as [$slug, $judul, $prioritas, $mulai, $durasi]) {
            if (! $s = $mapel($slug)) {
                continue;
            }

            Exam::query()->updateOrCreate(
                ['classroom_id' => $kelas->id, 'subject_id' => $s->id, 'title' => $judul],
                [
                    'priority' => $prioritas,
                    'starts_at' => $mulai,
                    'ends_at' => (clone $mulai)->addMinutes($durasi),
                ],
            );
        }

        // Ujian yang sudah selesai beserta nilainya.
        foreach ([['fisika', 'Penilaian Akhir Semester', 92, 12], ['biologi', 'Penilaian Akhir Semester', 85, 16]] as [$slug, $judul, $nilai, $hariLalu]) {
            if (! $s = $mapel($slug)) {
                continue;
            }

            $exam = Exam::query()->updateOrCreate(
                ['classroom_id' => $kelas->id, 'subject_id' => $s->id, 'title' => $judul],
                [
                    'starts_at' => now()->subDays($hariLalu),
                    'ends_at' => now()->subDays($hariLalu)->addMinutes(120),
                ],
            );

            ExamResult::query()->updateOrCreate(
                ['exam_id' => $exam->id, 'student_id' => $siswa->id],
                ['score' => $nilai, 'finished_at' => now()->subDays($hariLalu)->addMinutes(95)],
            );
        }

        $this->soalCbt($kelas);
    }

    /**
     * Sesi CBT Akidah Akhlak. Soal nomor 14 memakai teks asli dari
     * design-siswa.md; 39 sisanya dibuat sebagai pengisi agar navigator
     * 40 nomor punya isi yang bisa dibuka.
     */
    private function soalCbt(Classroom $kelas): void
    {
        $mapel = Subject::query()->where('slug', 'akidah-akhlak')->first();

        if (! $mapel) {
            return;
        }

        $exam = Exam::query()->updateOrCreate(
            [
                'classroom_id' => $kelas->id,
                'subject_id' => $mapel->id,
                'title' => 'Penilaian Akhir Semester (PAS) Ganjil',
            ],
            [
                'starts_at' => now()->subMinutes(18),
                'ends_at' => now()->addMinutes(102),
            ],
        );

        $asli = [
            'body' => 'Sifat terpuji yang dimiliki oleh Nabi Ibrahim AS ketika menghadapi cobaan dari Allah SWT berupa perintah untuk menyembelih putra tercintanya, Ismail AS, menunjukkan tingkatan iman yang sangat tinggi. Perilaku ini dalam terminologi Akidah Akhlak disebut sebagai...',
            'options' => [
                ['A', 'Sabar dan Tawakal yang Mutlak', true],
                ['B', 'Ukhuwah Islamiyah', false],
                ['C', "Syaja'ah dalam berdakwah", false],
                ['D', 'Istiqomah dalam Ibadah', false],
                ['E', 'Tasamuh antar sesama', false],
            ],
        ];

        for ($n = 1; $n <= 40; $n++) {
            $isAsli = $n === 14;

            $q = ExamQuestion::query()->updateOrCreate(
                ['exam_id' => $exam->id, 'number' => $n],
                [
                    'type' => 'Pilihan Ganda',
                    'body' => $isAsli
                        ? $asli['body']
                        : "Contoh soal Akidah Akhlak nomor {$n}. Teks soal sesungguhnya diisi guru lewat panel admin.",
                ],
            );

            $opsi = $isAsli ? $asli['options'] : [
                ['A', 'Pilihan A', $n % 5 === 1],
                ['B', 'Pilihan B', $n % 5 === 2],
                ['C', 'Pilihan C', $n % 5 === 3],
                ['D', 'Pilihan D', $n % 5 === 4],
                ['E', 'Pilihan E', $n % 5 === 0],
            ];

            foreach ($opsi as [$key, $body, $benar]) {
                ExamQuestionOption::query()->updateOrCreate(
                    ['exam_question_id' => $q->id, 'key' => $key],
                    ['body' => $body, 'is_correct' => $benar],
                );
            }
        }
    }

    /** design-siswa.md §7 — koleksi, produk baru, dan pinjaman berjalan. */
    private function perpustakaan(Student $siswa): void
    {
        // Penulis sengaja null untuk buku teks: design-siswa.md tidak
        // menyebutkannya, dan mengarang nama penulis lebih buruk daripada
        // membiarkannya kosong.
        // Urutan penting: "Produk Baru" di portal = empat buku yang paling
        // terakhir ditambahkan, jadi keempat judul itu ditaruh di akhir daftar.
        $katalog = [
            ['Informatika Kelas XI', null, null, 'Sains & Teknologi', 210],
            ['Matematika TL Kelas XI', null, null, 'Sains & Teknologi', 256],
            ['Akidah Akhlak Kelas XI', null, null, 'Studi Islam', 164],
            [
                'Zaman Keemasan Islam: Rumah Kebijaksanaan',
                null,
                'Telusuri kisah Baitul Hikmah di Baghdad, pusat penerjemahan dan ilmu pengetahuan yang mempertemukan tradisi Yunani, Persia, dan India dalam satu peradaban intelektual.',
                'Humaniora',
                350,
            ],
            ['Fisika Kelas XI', null, null, 'Sains & Teknologi', 240],
            ['Kimia Kelas XI', null, null, 'Sains & Teknologi', 228],
            ['Bahasa Arab Kelas XI', null, null, 'Studi Islam', 196],
            ['Digital Creativity', 'Creative Hub Team', null, 'Sains & Teknologi', 180],
        ];

        $buku = [];

        foreach ($katalog as $i => [$judul, $penulis, $sinopsis, $kategori, $halaman]) {
            $buku[$judul] = Book::query()->updateOrCreate(
                ['title' => $judul],
                [
                    // Kode contoh untuk meja sirkulasi: PUS-001, PUS-002, …
                    'code' => sprintf('PUS-%03d', $i + 1),
                    'author' => $penulis,
                    'description' => $sinopsis,
                    'category' => $kategori,
                    'total_pages' => $halaman,
                ],
            );
        }

        $pinjaman = [
            ['Informatika Kelas XI', 2, 0],
            ['Matematika TL Kelas XI', 5, 0],
            ['Akidah Akhlak Kelas XI', 2, 0],
            // Yang sedang dibaca — halaman 142 dari 350 = 41%, dibulatkan 42%
            // di rancangan.
            ['Zaman Keemasan Islam: Rumah Kebijaksanaan', 14, 142],
        ];

        foreach ($pinjaman as [$judul, $jatuhTempo, $halaman]) {
            BookLoan::query()->updateOrCreate(
                ['book_id' => $buku[$judul]->id, 'student_id' => $siswa->id],
                ['due_on' => now()->addDays($jatuhTempo), 'current_page' => $halaman, 'returned_at' => null],
            );
        }
    }

    /** design-siswa.md §8 — kategori, diskusi, dan balasan. */
    private function forum(Student $siswa): void
    {
        $kategori = [
            ['Diskusi Akademik', 'Topik tentang mata pelajaran, ujian, dan persiapan olimpiade.', 'book', 'blue'],
            ['Ekstrakulikuler', 'Klub, olahraga, seni, dan kegiatan organisasi mahasiswa.', 'ball', 'gold'],
            ['Madrasah Life', 'Kegiatan keagamaan, pembentukan karakter, dan suasana sekolah.', 'heart', 'teal'],
        ];

        $kat = [];

        foreach ($kategori as [$nama, $desc, $ikon, $warna]) {
            $kat[$nama] = ForumCategory::query()->updateOrCreate(
                ['slug' => Str::slug($nama)],
                ['name' => $nama, 'description' => $desc, 'icon' => $ikon, 'tone' => $warna],
            );
        }

        // Penulis diskusi. Dibuat sebagai siswa nonaktif tanpa kelas supaya
        // tidak mengacaukan hitungan "Siswa Aktif" di dasbor admin.
        $penulis = [];

        foreach ([['@Tumbal_pakaL', '900000001'], ['@FINEshyt', '900000002'], ['@fian_na_imo', '900000003']] as [$nama, $nisn]) {
            $penulis[$nama] = Student::query()->updateOrCreate(
                ['nisn' => $nisn],
                ['name' => $nama, 'password' => Str::random(32), 'classroom_id' => null, 'is_active' => false],
            );
        }

        $diskusi = [
            ['Diskusi Akademik', '@Tumbal_pakaL', 'Strategi Belajar Efektif Menjelang Ujian Tengah Semester',
                'Mari berbagi tips membagi waktu belajar dan kegiatan ekskul agar nilai tetap maksimal di tengah jadwal yang padat bulan ini.', 82, 24, 2],
            ['Madrasah Life', '@FINEshyt', 'Cara cepat naik rank immortal di season 41',
                'Saran Hero buat push rank dong suhuu, tiap hari stuck di epic terus huhu', 82, 48, 5],
            ['Madrasah Life', '@fian_na_imo', 'Tips Menyeimbangkan Tugas Sekolah dan Target Hafalan',
                'Banyak yang bertanya bagaimana cara mengatur jadwal antara setumpuk PR dan setoran hafalan harian. Berikut beberapa insight...', 82, 12, 24],
        ];

        foreach ($diskusi as [$namaKat, $namaPenulis, $judul, $isi, $suka, $jumlahBalasan, $jamLalu]) {
            $thread = ForumThread::query()->updateOrCreate(
                ['title' => $judul],
                [
                    'forum_category_id' => $kat[$namaKat]->id,
                    'student_id' => $penulis[$namaPenulis]->id,
                    'body' => $isi,
                    'like_count' => $suka,
                    'created_at' => now()->subHours($jamLalu),
                ],
            );

            // Balasan dibuat secukupnya agar hitungannya cocok dengan rancangan.
            $adaSekarang = $thread->replies()->count();

            for ($i = $adaSekarang; $i < $jumlahBalasan; $i++) {
                ForumReply::query()->create([
                    'forum_thread_id' => $thread->id,
                    'student_id' => $siswa->id,
                    'body' => 'Balasan contoh untuk melengkapi hitungan diskusi.',
                ]);
            }
        }
    }
}
