<?php

namespace Tests\Feature;

use App\Filament\Pages\Modules\Kesiswaan;
use App\Filament\Resources\DisciplineRecords\Pages\ListDisciplineRecords;
use App\Filament\Resources\DisciplineRules\Pages\ListDisciplineRules;
use App\Models\DisciplineRecord;
use App\Models\DisciplineRule;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminKesiswaanTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_the_rule_and_record_pages_render(): void
    {
        DisciplineRule::factory()->count(2)->create();
        DisciplineRecord::factory()->count(2)->create();

        Livewire::test(ListDisciplineRules::class)->assertSuccessful();
        Livewire::test(ListDisciplineRecords::class)->assertSuccessful();
    }

    /** Penghargaan mengurangi poin, pelanggaran menambahnya. */
    public function test_an_award_carries_negative_points(): void
    {
        $pelanggaran = DisciplineRule::factory()->create(['points' => 20]);
        $penghargaan = DisciplineRule::factory()->penghargaan()->create(['points' => 15]);

        $this->assertSame(20, $pelanggaran->signedPoints());
        $this->assertSame(-15, $penghargaan->signedPoints());
    }

    /**
     * Poin disalin ke catatan saat kejadian dicatat. Mengubah bobot aturan
     * setelahnya tidak boleh menulis ulang klasemen yang sudah lewat.
     */
    public function test_changing_a_rule_does_not_rewrite_past_records(): void
    {
        $rule = DisciplineRule::factory()->create(['points' => 10]);
        $record = DisciplineRecord::factory()->for($rule, 'rule')->create(['points' => 10]);

        $rule->update(['points' => 99]);

        $this->assertSame(10, $record->refresh()->points);
    }

    public function test_the_ranking_sums_points_per_student(): void
    {
        $siswa = Student::factory()->create();
        DisciplineRecord::factory()->for($siswa)->create(['points' => 20]);
        DisciplineRecord::factory()->for($siswa)->create(['points' => 15]);
        // Penghargaan mengurangi total.
        DisciplineRecord::factory()->for($siswa)->create(['points' => -5]);

        $hasil = Student::query()
            ->whereKey($siswa->id)
            ->withSum('disciplineRecords as poin', 'points')
            ->first();

        $this->assertSame(30, (int) $hasil->poin);

        Livewire::test(Kesiswaan::class)->assertSuccessful();
    }

    /** Siswa tanpa catatan tetap muncul, dengan nol poin. */
    public function test_a_student_without_records_still_appears(): void
    {
        Student::factory()->create(['name' => 'Tanpa Catatan']);

        Livewire::test(Kesiswaan::class)
            ->assertSuccessful()
            ->assertSee('Tanpa Catatan');
    }
}
