<?php

namespace Tests\Feature;

use App\Filament\Pages\Modules\LiveMonitoring;
use App\Filament\Pages\Modules\RekapAbsensi;
use App\Filament\Resources\Attendances\Pages\ListAttendances;
use App\Filament\Resources\StudentPermits\Pages\ListStudentPermits;
use App\Filament\Resources\TeachingJournals\Pages\ListTeachingJournals;
use App\Models\Attendance;
use App\Models\Student;
use App\Models\StudentPermit;
use App\Models\TeachingJournal;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAbsensiTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_the_attendance_pages_render(): void
    {
        Attendance::factory()->count(2)->create();
        StudentPermit::factory()->count(2)->create();
        TeachingJournal::factory()->count(2)->create();

        Livewire::test(ListAttendances::class)->assertSuccessful();
        Livewire::test(ListStudentPermits::class)->assertSuccessful();
        Livewire::test(ListTeachingJournals::class)->assertSuccessful();
    }

    /** Satu siswa tidak boleh punya dua baris kehadiran pada tanggal sama. */
    public function test_a_student_has_at_most_one_record_per_day(): void
    {
        $siswa = Student::factory()->create();
        Attendance::factory()->for($siswa)->create(['date' => '2026-09-21']);

        $this->expectException(QueryException::class);
        Attendance::factory()->for($siswa)->create(['date' => '2026-09-21']);
    }

    public function test_the_monthly_matrix_lists_students_and_their_codes(): void
    {
        $siswa = Student::factory()->create(['name' => 'Siswa Matriks']);
        Attendance::factory()->for($siswa)->create([
            'date' => now()->startOfMonth()->toDateString(),
            'code' => 'T',
        ]);

        $page = Livewire::test(RekapAbsensi::class)->assertSuccessful();

        $baris = $page->instance()->getBaris();
        $this->assertCount(1, $baris);
        $this->assertSame('T', $baris->first()['kode'][1]);
        $this->assertSame(1, $baris->first()['rekap']['T']);
    }

    public function test_live_monitoring_counts_today_and_lists_students_still_out(): void
    {
        $hadir = Student::factory()->create();
        Attendance::factory()->for($hadir)->create(['date' => now()->toDateString(), 'code' => 'H']);

        $keluar = Student::factory()->create(['name' => 'Masih Di Luar']);
        StudentPermit::factory()->for($keluar)->create();
        StudentPermit::factory()->returned()->create();

        $page = Livewire::test(LiveMonitoring::class)->assertSuccessful();
        $ringkasan = $page->instance()->getRingkasan();

        $this->assertSame(1, $ringkasan['Hadir']);
        // Siswa kedua belum punya catatan kehadiran sama sekali.
        $this->assertSame(0, $ringkasan['Alpha']);
        $this->assertGreaterThanOrEqual(1, $ringkasan['Belum Tercatat']);

        $page->assertSee('Masih Di Luar');
    }
}
