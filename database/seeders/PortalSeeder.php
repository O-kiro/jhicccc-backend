<?php

namespace Database\Seeders;

use App\Models\Announcement;
use App\Models\Assessment;
use App\Models\Classroom;
use App\Models\ReportCard;
use App\Models\Schedule;
use App\Models\Student;
use App\Models\Subject;
use App\Models\Teacher;
use App\Models\TeacherFeedback;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

/**
 * Mengisi database dengan data contoh dari design-siswa.md supaya portal
 * Next.js punya isi yang sama persis dengan rancangannya.
 */
class PortalSeeder extends Seeder
{
    public function run(): void
    {
        $this->createAdmin();

        $teachers = $this->createTeachers();
        $subjects = $this->createSubjects();
        $classroom = $this->createClassroom($teachers);
        $student = $this->createStudent($classroom);

        $this->createSchedule($classroom, $subjects, $teachers);
        $this->createAnnouncements();
        $this->createReportCard($student);
        $this->createAssessments($student, $subjects);
        $this->createTeacherFeedback($student, $teachers);
    }

    private function createAdmin(): void
    {
        User::query()->updateOrCreate(
            ['email' => 'admin@mankotabatu.sch.id'],
            ['name' => 'Admin Madrasah', 'password' => 'password', 'role' => 'super_admin'],
        );
    }

    /**
     * @return array<string, Teacher>
     */
    private function createTeachers(): array
    {
        $names = [
            'abdurrahman' => 'Ust. H. Abdurrahman',
            'rini' => 'Rini Waraswati, S.Pd, M.Si',
            'sukrawati' => 'Dra. Sukrawati Arni',
            'indah' => 'Indah Rahmayanti, S.Pd',
            'aslanik' => 'Aslanik, S.Pd.I',
            'ahmad_fauzi' => 'Ustadz Ahmad Fauzi, M.Ag',
            'ani' => 'Ani Nur Aisyah, S.Ag',
            'anisak' => 'Anisak Intan Eka Prani, M.Si',
            'siti' => 'Siti Muthomimah, S.Pd',
            'ahmad_fauzan' => 'Ahmad Fauzan, M.Pd',
        ];

        $teachers = [];

        foreach ($names as $key => $name) {
            $teachers[$key] = Teacher::query()->updateOrCreate(['name' => $name]);
        }

        return $teachers;
    }

    /**
     * @return array<string, Subject>
     */
    private function createSubjects(): array
    {
        $rows = [
            ['Quran Hadist', 'Agama', 'tahfidz', 'teal'],
            ['Fiqih', 'Agama', 'book', 'teal'],
            ['Akidah Akhlak', 'Agama', 'tahfidz', 'teal'],
            ['Sejarah Kebudayaan Islam', 'Agama', 'book', 'teal'],
            ['Bahasa Arab', 'Agama', 'globe', 'gold'],
            ['Matematika', 'Sains', 'sigma', 'blue'],
            ['Kimia', 'Sains', 'flask', 'blue'],
            ['Fisika', 'Sains', 'research', 'blue'],
            ['Biologi', 'Sains', 'leaf', 'teal'],
            ['Bahasa Inggris', 'Sains', 'globe', 'gold'],
        ];

        $subjects = [];

        foreach ($rows as [$name, $category, $icon, $tone]) {
            $slug = Str::slug($name);
            $subjects[$slug] = Subject::query()->updateOrCreate(
                ['slug' => $slug],
                ['name' => $name, 'category' => $category, 'icon' => $icon, 'tone' => $tone],
            );
        }

        return $subjects;
    }

    /**
     * @param  array<string, Teacher>  $teachers
     */
    private function createClassroom(array $teachers): Classroom
    {
        return Classroom::query()->updateOrCreate(
            ['name' => 'X-B'],
            [
                'level' => 'X',
                'academic_year' => '2025/2026',
                'homeroom_teacher_id' => $teachers['siti']->id,
            ],
        );
    }

    private function createStudent(Classroom $classroom): Student
    {
        return Student::query()->updateOrCreate(
            ['nisn' => '009283741'],
            [
                'name' => 'Akhnaf Meyfan',
                'password' => 'password',
                'classroom_id' => $classroom->id,
                'streak_days' => 14,
                'is_active' => true,
            ],
        );
    }

    /**
     * Jadwal yang sama diulang Senin–Jumat supaya "Jadwal Hari Ini" di portal
     * selalu terisi pada hari kerja mana pun.
     *
     * @param  array<string, Subject>  $subjects
     * @param  array<string, Teacher>  $teachers
     */
    private function createSchedule(Classroom $classroom, array $subjects, array $teachers): void
    {
        $sessions = [
            ['fiqih', 'abdurrahman', '07:00:00', '08:30:00'],
            ['matematika', 'rini', '08:30:00', '09:45:00'],
            ['kimia', 'sukrawati', '09:45:00', '11:45:00'],
            ['bahasa-arab', 'indah', '12:30:00', '13:50:00'],
            ['sejarah-kebudayaan-islam', 'aslanik', '13:50:00', '14:45:00'],
        ];

        foreach (range(1, 5) as $day) {
            foreach ($sessions as [$subjectSlug, $teacherKey, $startsAt, $endsAt]) {
                Schedule::query()->updateOrCreate(
                    [
                        'classroom_id' => $classroom->id,
                        'subject_id' => $subjects[$subjectSlug]->id,
                        'day_of_week' => $day,
                        'starts_at' => $startsAt,
                    ],
                    [
                        'teacher_id' => $teachers[$teacherKey]->id,
                        'ends_at' => $endsAt,
                        'meeting_url' => 'https://meet.google.com/lookup/makoba-'.$subjectSlug,
                    ],
                );
            }
        }
    }

    private function createAnnouncements(): void
    {
        $rows = [
            ['Kebijakan Seragam Sekolah', 'Kebijakan baru tentang seragam', '2026-03-15'],
            ['Kompetisi Robotik', 'Pendaftaran untuk turnamen robotika tahunan tingkat sekolah', '2026-03-12'],
            ['Pertemuan Orang Tua dan Guru', 'Rapat evaluasi bulanan untuk orang tua akan diadakan pada hari Sabtu ini.', '2026-03-10'],
        ];

        foreach ($rows as [$title, $body, $publishedAt]) {
            Announcement::query()->updateOrCreate(
                ['title' => $title],
                ['body' => $body, 'published_at' => $publishedAt],
            );
        }
    }

    private function createReportCard(Student $student): void
    {
        ReportCard::query()->updateOrCreate(
            [
                'student_id' => $student->id,
                'academic_year' => '2025/2026',
                'semester' => 'Ganjil',
            ],
            [
                'average_score' => 88.30,
                'class_rank' => 2,
                'class_size' => 32,
                'attendance_percentage' => 98.50,
            ],
        );
    }

    /**
     * Nilai bulanan Agustus–Januari membentuk grafik "Sejarah Nilai",
     * sementara tiga nilai terakhir mengisi "Penilaian Terbaru".
     *
     * @param  array<string, Subject>  $subjects
     */
    private function createAssessments(Student $student, array $subjects): void
    {
        $history = [
            ['2025-08-20', 82], ['2025-09-18', 85], ['2025-10-21', 84],
            ['2025-11-19', 89], ['2025-12-16', 91],
        ];

        foreach ($history as [$date, $score]) {
            Assessment::query()->updateOrCreate(
                [
                    'student_id' => $student->id,
                    'subject_id' => $subjects['matematika']->id,
                    'assessed_on' => $date,
                ],
                ['title' => 'Penilaian Harian', 'score' => $score],
            );
        }

        $latest = [
            ['quran-hadist', 94],
            ['matematika', 88],
            ['fisika', 91],
        ];

        foreach ($latest as [$slug, $score]) {
            Assessment::query()->updateOrCreate(
                [
                    'student_id' => $student->id,
                    'subject_id' => $subjects[$slug]->id,
                    'assessed_on' => '2026-01-15',
                ],
                ['title' => 'Penilaian Akhir Semester', 'score' => $score],
            );
        }
    }

    /**
     * @param  array<string, Teacher>  $teachers
     */
    private function createTeacherFeedback(Student $student, array $teachers): void
    {
        $rows = [
            ['siti', 'Wali Kelas', 'Akhnaf telah menunjukkan perkembangan luar biasa dalam kemampuan kepemimpinannya semester ini. Kontribusinya di forum kelas sangat keren.'],
            ['ahmad_fauzan', 'Guru Quran Hadist', 'Performa menghafal yang luar biasa. Terus pertahankan konsistensimu dalam belajar Al-Quran dan Hadist'],
            ['anisak', 'Guru Fisika', 'Dalam pelajaran Fisika, Akhnaf menunjukkan pemahaman konsep yang cukup baik, terutama saat praktikum. Ia aktif mengamati dan mengaitkan teori dengan fenomena yang terjadi di sekitarnya.'],
            ['rini', 'Guru Matematika', 'Akhnaf menunjukkan perkembangan yang baik dalam pembelajaran Matematika semester ini. Ia semakin percaya diri menyelesaikan soal-soal hitungan dan tidak ragu bertanya saat menemui kesulitan.'],
        ];

        foreach ($rows as [$teacherKey, $role, $body]) {
            TeacherFeedback::query()->updateOrCreate(
                [
                    'student_id' => $student->id,
                    'teacher_id' => $teachers[$teacherKey]->id,
                    'role' => $role,
                ],
                ['body' => $body],
            );
        }
    }
}
