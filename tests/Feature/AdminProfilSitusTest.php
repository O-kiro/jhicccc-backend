<?php

namespace Tests\Feature;

use App\Filament\Pages\ProfilSitus;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminProfilSitusTest extends TestCase
{
    use RefreshDatabase;

    public function test_halaman_profil_bisa_dibuka_dan_disimpan(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ProfilSitus::class)
            ->assertOk()
            ->set('data.principal_name', 'Drs. H. Farhadi, M.Si')
            ->set('data.principal_role', 'Kepala MAN Kota Batu')
            ->set('data.building_photo', '/photos/gedung-madrasah.jpg')
            ->call('simpan')
            ->assertHasNoErrors();

        $this->assertDatabaseHas('site_profiles', [
            'principal_name' => 'Drs. H. Farhadi, M.Si',
            'building_photo' => '/photos/gedung-madrasah.jpg',
        ]);

        $this->getJson('/api/v1/public/site')
            ->assertOk()
            ->assertJsonPath('profile.principalName', 'Drs. H. Farhadi, M.Si')
            ->assertJsonPath('profile.buildingPhoto', '/photos/gedung-madrasah.jpg');
    }

    public function test_nomor_whatsapp_tersimpan_dan_dikirim_ke_situs(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ProfilSitus::class)
            ->set('data.whatsapp', '0812-3456-7890')
            ->call('simpan')
            ->assertHasNoErrors();

        $this->getJson('/api/v1/public/site')
            ->assertJsonPath('profile.whatsapp', '0812-3456-7890');
    }

    public function test_nomor_whatsapp_yang_bukan_angka_ditolak(): void
    {
        $this->actingAs(User::factory()->create());

        Livewire::test(ProfilSitus::class)
            ->set('data.whatsapp', 'hubungi admin')
            ->call('simpan')
            ->assertHasErrors(['data.whatsapp']);
    }
}
