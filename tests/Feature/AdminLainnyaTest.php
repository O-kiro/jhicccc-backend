<?php

namespace Tests\Feature;

use App\Filament\Resources\AssetBookings\Pages\CreateAssetBooking;
use App\Filament\Resources\AssetBookings\Pages\ListAssetBookings;
use App\Filament\Resources\Assets\Pages\ListAssets;
use App\Filament\Resources\CounselingSessions\Pages\ListCounselingSessions;
use App\Filament\Resources\IntegrityDocuments\Pages\ListIntegrityDocuments;
use App\Filament\Resources\Letters\Pages\ListLetters;
use App\Filament\Resources\Rooms\Pages\ListRooms;
use App\Models\Asset;
use App\Models\AssetBooking;
use App\Models\CounselingSession;
use App\Models\IntegrityDocument;
use App\Models\Letter;
use App\Models\Room;
use App\Models\User;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminLainnyaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->actingAs(User::factory()->create());
    }

    /**
     * @return array<string, array{class-string, class-string}>
     */
    public static function halaman(): array
    {
        return [
            'Master Sarpras' => [ListRooms::class, Room::class],
            'Buku Induk Barang' => [ListAssets::class, Asset::class],
            'Peminjaman & Booking' => [ListAssetBookings::class, AssetBooking::class],
            'Persuratan' => [ListLetters::class, Letter::class],
            'Konseling' => [ListCounselingSessions::class, CounselingSession::class],
            'Kelola ZI' => [ListIntegrityDocuments::class, IntegrityDocument::class],
        ];
    }

    /**
     * @param  class-string  $page
     * @param  class-string  $model
     */
    #[DataProvider('halaman')]
    public function test_the_list_page_renders_with_records(string $page, string $model): void
    {
        $model::factory()->count(2)->create();

        Livewire::test($page)->assertSuccessful();
    }

    /** Peminjaman tanpa ruang maupun barang tidak masuk akal. */
    public function test_a_booking_needs_a_room_or_an_asset(): void
    {
        Livewire::test(CreateAssetBooking::class)
            ->fillForm([
                'borrower' => 'OSIS',
                'purpose' => 'Rapat',
                'starts_at' => now()->addHour(),
                'ends_at' => now()->addHours(2),
            ])
            ->call('create')
            ->assertHasFormErrors(['room_id', 'asset_id']);
    }

    /** Ruang dan barang sekaligus membuat laporan pemakaian tumpang tindih. */
    public function test_a_booking_cannot_take_a_room_and_an_asset_at_once(): void
    {
        $room = Room::factory()->create();
        $asset = Asset::factory()->create();

        Livewire::test(CreateAssetBooking::class)
            ->fillForm([
                'room_id' => $room->id,
                'asset_id' => $asset->id,
                'borrower' => 'OSIS',
                'purpose' => 'Rapat',
                'starts_at' => now()->addHour(),
                'ends_at' => now()->addHours(2),
            ])
            ->call('create')
            ->assertHasFormErrors();

        $this->assertSame(0, AssetBooking::query()->count());
    }

    public function test_a_room_booking_is_saved(): void
    {
        $room = Room::factory()->create(['name' => 'Aula Utama']);

        Livewire::test(CreateAssetBooking::class)
            ->fillForm([
                'room_id' => $room->id,
                'borrower' => 'OSIS',
                'purpose' => 'Rapat pleno',
                'starts_at' => now()->addHour(),
                'ends_at' => now()->addHours(2),
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->assertSame('Aula Utama', AssetBooking::query()->sole()->subjectName());
    }

    /** Nomor surat unik per jenis: surat masuk boleh bernomor sama dengan surat keluar. */
    public function test_letter_numbers_are_unique_per_direction(): void
    {
        Letter::factory()->create(['direction' => 'masuk', 'number' => '001/X/2026']);
        Letter::factory()->create(['direction' => 'keluar', 'number' => '001/X/2026']);

        $this->assertSame(2, Letter::query()->count());

        $this->expectException(QueryException::class);
        Letter::factory()->create(['direction' => 'masuk', 'number' => '001/X/2026']);
    }

    /** Isi catatan rahasia tidak boleh terbaca dari daftar. */
    public function test_confidential_counseling_notes_are_hidden_in_the_list(): void
    {
        CounselingSession::factory()->create([
            'summary' => 'Isi yang tidak boleh tampil di daftar',
            'is_confidential' => true,
        ]);

        Livewire::test(ListCounselingSessions::class)
            ->assertDontSee('Isi yang tidak boleh tampil di daftar')
            ->assertSee('rahasia');
    }
}
