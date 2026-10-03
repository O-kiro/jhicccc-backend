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
}
