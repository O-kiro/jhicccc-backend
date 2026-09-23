<?php

namespace Tests\Feature;

use App\Filament\Resources\AlumniAccounts\Pages\CreateAlumniAccount;
use App\Filament\Resources\AlumniAccounts\Pages\EditAlumniAccount;
use App\Filament\Resources\AlumniOutcomes\Pages\CreateAlumniOutcome;
use App\Models\AlumniAccount;
use App\Models\AlumniOutcome;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Livewire\Livewire;
use Tests\TestCase;

class AdminAlumniTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_the_admin_gives_portal_access_and_a_blank_password_keeps_it(): void
    {
        Livewire::test(CreateAlumniAccount::class)
            ->fillForm([
                'name' => 'Alumni Baru',
                'email' => 'alumni.baru@madrasah.test',
                'graduation_year' => 2020,
                'password' => 'awal12345',
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $alumni = AlumniAccount::query()->where('email', 'alumni.baru@madrasah.test')->sole();
        $this->assertTrue(Hash::check('awal12345', $alumni->password));

        Livewire::test(EditAlumniAccount::class, ['record' => $alumni->getRouteKey()])
            ->assertSchemaStateSet(['password' => null])
            ->fillForm(['occupation' => 'Mahasiswa UB', 'password' => ''])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertTrue(Hash::check('awal12345', $alumni->refresh()->password));
        $this->assertSame('Mahasiswa UB', $alumni->occupation);
    }

    /**
     * Pasangan tahun–kategori unik di basis data. Tanpa aturan di formulir,
     * penyimpanan kedua akan menabrak indeks dan balas 500.
     */
    public function test_the_same_category_cannot_be_recorded_twice_in_one_year(): void
    {
        AlumniOutcome::factory()->create(['year' => 2026, 'category' => 'ptn', 'students' => 100]);

        Livewire::test(CreateAlumniOutcome::class)
            ->fillForm(['year' => 2026, 'category' => 'ptn', 'students' => 50])
            ->call('create')
            ->assertHasFormErrors(['category']);

        // Tahun lain tetap boleh.
        Livewire::test(CreateAlumniOutcome::class)
            ->fillForm(['year' => 2025, 'category' => 'ptn', 'students' => 50])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame(2, AlumniOutcome::query()->count());
    }
}
