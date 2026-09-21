<?php

namespace Tests\Feature;

use App\Filament\Resources\GuestVisits\Pages\ListGuestVisits;
use App\Filament\Resources\ServiceRequests\Pages\ListServiceRequests;
use App\Filament\Resources\Services\Pages\ListServices;
use App\Models\GuestVisit;
use App\Models\Service;
use App\Models\ServiceRequest;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use Tests\TestCase;

class AdminHumasTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    public function test_the_humas_pages_render(): void
    {
        Service::factory()->count(2)->create();
        GuestVisit::factory()->count(2)->create();
        ServiceRequest::factory()->count(2)->create();

        Livewire::test(ListServices::class)->assertSuccessful();
        Livewire::test(ListGuestVisits::class)->assertSuccessful();
        Livewire::test(ListServiceRequests::class)->assertSuccessful();
    }

    /**
     * Katalognya satu tabel untuk dua modul, jadi tiap sisi harus hanya
     * menawarkan jenis yang relevan.
     */
    public function test_the_catalogue_separates_visits_from_ptsp_services(): void
    {
        Service::factory()->create(['name' => 'Legalisir Ijazah', 'kind' => 'layanan']);
        Service::factory()->kunjungan()->create(['name' => 'Studi Banding']);

        $this->assertSame(1, Service::query()->where('kind', 'layanan')->count());
        $this->assertSame(1, Service::query()->where('kind', 'kunjungan')->count());
    }

    public function test_marking_a_visit_finished_stamps_the_time(): void
    {
        $visit = GuestVisit::factory()->create(['status' => 'pending', 'finished_at' => null]);

        Livewire::test(ListGuestVisits::class)
            ->callTableAction('selesaikan', $visit)
            ->assertHasNoTableActionErrors();

        $visit->refresh();
        $this->assertSame('selesai', $visit->status);
        $this->assertNotNull($visit->finished_at);
    }

    public function test_marking_a_request_finished_stamps_the_time(): void
    {
        $request = ServiceRequest::factory()->create(['status' => 'baru']);

        Livewire::test(ListServiceRequests::class)
            ->callTableAction('selesaikan', $request)
            ->assertHasNoTableActionErrors();

        $request->refresh();
        $this->assertSame('selesai', $request->status);
        $this->assertNotNull($request->completed_at);
    }
}
