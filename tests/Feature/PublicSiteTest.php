<?php

namespace Tests\Feature;

use App\Models\AgendaItem;
use App\Models\DigitalService;
use App\Models\NewsPost;
use App\Models\SitePopup;
use Database\Seeders\SitusSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    /** @return array<string, mixed> */
    private function asli(): array
    {
        return json_decode((string) file_get_contents(database_path('seeders/data/situs.json')), true);
    }

    /**
     * Setelah beralih ke CMS, situs harus tampil persis seperti sebelumnya.
     * Satu-satunya perbedaan yang disengaja: berita diurutkan berdasarkan
     * tanggal, sehingga berita baru otomatis menjadi berita utama.
     */
    public function test_the_api_reproduces_the_original_site_content(): void
    {
        $this->seed(SitusSeeder::class);
        $asli = $this->asli();

        $respons = $this->getJson(route('api.v1.public.site'))->assertOk()->json();

        foreach (array_keys($asli) as $jenis) {
            if ($jenis === 'news') {
                continue;
            }

            $this->assertSame($asli[$jenis], $respons[$jenis], "Konten {$jenis} berubah setelah beralih ke CMS.");
        }

        // Berita: isinya sama, hanya urutannya kronologis. Urutan kunci di
        // dalam tiap objek juga dinormalkan — bagi JavaScript tidak berarti.
        $urut = fn (array $xs): array => collect($xs)
            ->map(function (array $x): array {
                ksort($x);

                return $x;
            })
            ->sortBy('slug')
            ->values()
            ->all();
        $this->assertSame($urut($asli['news']), $urut($respons['news']));
        $this->assertSame($asli['news'][0]['slug'], $respons['news'][0]['slug'], 'Berita utama berubah.');
    }

    /** Menjalankan seeder dua kali tidak boleh menggandakan isi. */
    public function test_the_seeder_is_idempotent(): void
    {
        $this->seed(SitusSeeder::class);
        $sekali = $this->getJson(route('api.v1.public.site'))->json();

        $this->seed(SitusSeeder::class);
        $dua = $this->getJson(route('api.v1.public.site'))->json();

        $this->assertSame($sekali, $dua);
        $this->assertSame(count($this->asli()['agenda']), AgendaItem::query()->count());
    }

    public function test_it_needs_no_authentication(): void
    {
        $this->getJson(route('api.v1.public.site'))->assertOk();
    }

    public function test_unpublished_and_future_news_stay_hidden(): void
    {
        NewsPost::factory()->create(['slug' => 'terbit', 'published_on' => now()->subDay()]);
        NewsPost::factory()->create(['slug' => 'draf', 'is_published' => false]);
        NewsPost::factory()->create(['slug' => 'besok', 'published_on' => now()->addDay()]);

        $slug = collect($this->getJson(route('api.v1.public.site'))->json('news'))->pluck('slug')->all();

        $this->assertSame(['terbit'], $slug);
    }

    public function test_the_newest_news_becomes_the_featured_one(): void
    {
        NewsPost::factory()->create(['slug' => 'lama', 'published_on' => now()->subWeek()]);
        NewsPost::factory()->create(['slug' => 'baru', 'published_on' => now()->subDay()]);

        $this->getJson(route('api.v1.public.site'))->assertJsonPath('news.0.slug', 'baru');
    }

    public function test_inactive_services_are_hidden(): void
    {
        DigitalService::factory()->create(['name' => 'Tampil']);
        DigitalService::factory()->create(['name' => 'Disembunyikan', 'is_active' => false]);

        $nama = collect($this->getJson(route('api.v1.public.site'))->json('digitalServices'))->pluck('name')->all();

        $this->assertSame(['Tampil'], $nama);
    }

    public function test_a_popup_only_shows_inside_its_window(): void
    {
        SitePopup::factory()->create(['title' => 'Kedaluwarsa', 'ends_at' => now()->subDay()]);
        SitePopup::factory()->create(['title' => 'Belum mulai', 'starts_at' => now()->addDay()]);
        SitePopup::factory()->create(['title' => 'Mati', 'is_active' => false]);

        $this->getJson(route('api.v1.public.site'))->assertJsonPath('popup', null);

        SitePopup::factory()->create(['title' => 'Berlaku', 'starts_at' => now()->subHour(), 'ends_at' => now()->addHour()]);

        $this->getJson(route('api.v1.public.site'))->assertJsonPath('popup.title', 'Berlaku');
    }
}
