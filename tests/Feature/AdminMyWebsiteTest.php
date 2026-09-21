<?php

namespace Tests\Feature;

use App\Filament\Resources\Achievements\Pages\ListAchievements;
use App\Filament\Resources\AgendaItems\Pages\ListAgendaItems;
use App\Filament\Resources\Alumnis\Pages\ListAlumnis;
use App\Filament\Resources\DigitalServices\DigitalServiceResource;
use App\Filament\Resources\DigitalServices\Pages\CreateDigitalService;
use App\Filament\Resources\DigitalServices\Pages\ListDigitalServices;
use App\Filament\Resources\Extracurriculars\Pages\ListExtracurriculars;
use App\Filament\Resources\Facilities\Pages\ListFacilities;
use App\Filament\Resources\Faqs\Pages\ListFaqs;
use App\Filament\Resources\GalleryItems\Pages\ListGalleryItems;
use App\Filament\Resources\NewsPosts\Pages\CreateNewsPost;
use App\Filament\Resources\NewsPosts\Pages\ListNewsPosts;
use App\Filament\Resources\Programs\Pages\ListPrograms;
use App\Filament\Resources\SitePopups\Pages\CreateSitePopup;
use App\Filament\Resources\SitePopups\Pages\ListSitePopups;
use App\Filament\Resources\Testimonials\Pages\ListTestimonials;
use App\Models\Achievement;
use App\Models\AgendaItem;
use App\Models\Alumni;
use App\Models\DigitalService;
use App\Models\Extracurricular;
use App\Models\Facility;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\NewsPost;
use App\Models\Program;
use App\Models\SitePopup;
use App\Models\Testimonial;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class AdminMyWebsiteTest extends TestCase
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
            'Pop-up' => [ListSitePopups::class, SitePopup::class],
            'Layanan Cepat' => [ListDigitalServices::class, DigitalService::class],
            'Berita' => [ListNewsPosts::class, NewsPost::class],
            'Agenda' => [ListAgendaItems::class, AgendaItem::class],
            'Program' => [ListPrograms::class, Program::class],
            'Prestasi' => [ListAchievements::class, Achievement::class],
            'Ekstrakurikuler' => [ListExtracurriculars::class, Extracurricular::class],
            'Fasilitas' => [ListFacilities::class, Facility::class],
            'Galeri' => [ListGalleryItems::class, GalleryItem::class],
            'QnA' => [ListFaqs::class, Faq::class],
            'Testimoni' => [ListTestimonials::class, Testimonial::class],
            'Alumni' => [ListAlumnis::class, Alumni::class],
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

    /** Alur utama CMS: ditulis di panel, muncul di situs. */
    public function test_news_written_in_the_panel_reaches_the_public_site(): void
    {
        Livewire::test(CreateNewsPost::class)
            ->fillForm([
                'title' => 'Berita Dari Panel Admin',
                'slug' => 'berita-dari-panel-admin',
                'category' => 'Pengumuman',
                'published_on' => now()->toDateString(),
                'author' => 'Humas MAKOBA',
                'tone' => 'teal',
                'excerpt' => 'Ringkasan singkat.',
                // Bentuk dalam Repeater sederhana: tiap butir dibungkus nama
                // kolomnya. fillForm() melewati tahap pembungkusan yang di
                // peramban dikerjakan Filament saat formulir dimuat.
                'content' => [['paragraf' => 'Paragraf pertama.'], ['paragraf' => 'Paragraf kedua.']],
                'is_published' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->getJson(route('api.v1.public.site'))
            ->assertOk()
            ->assertJsonPath('news.0.slug', 'berita-dari-panel-admin')
            ->assertJsonPath('news.0.content', ['Paragraf pertama.', 'Paragraf kedua.']);
    }

    /** Beranda hanya punya tempat untuk delapan kartu layanan. */
    public function test_no_more_than_eight_services_can_be_shown(): void
    {
        DigitalService::factory()->count(DigitalServiceResource::MAKS_AKTIF)->create();

        Livewire::test(CreateDigitalService::class)
            ->fillForm([
                'name' => 'Layanan Kesembilan',
                'icon' => 'rdm',
                'tone' => 'teal',
                'href' => '/layanan',
                'description' => 'Tidak muat di beranda.',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasFormErrors(['is_active']);

        // Tapi boleh disimpan dalam keadaan tidak tampil.
        Livewire::test(CreateDigitalService::class)
            ->fillForm([
                'name' => 'Layanan Kesembilan',
                'icon' => 'rdm',
                'tone' => 'teal',
                'href' => '/layanan',
                'description' => 'Disimpan dulu, belum tampil.',
                'is_active' => false,
            ])
            ->call('create')
            ->assertHasNoFormErrors();
    }

    public function test_a_popup_created_in_the_panel_shows_on_the_site(): void
    {
        Livewire::test(CreateSitePopup::class)
            ->fillForm([
                'title' => 'PPDB Dibuka',
                'body' => 'Pendaftaran gelombang dua dibuka.',
                'link_url' => '/ppdb',
                'link_label' => 'Daftar',
                'is_active' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $this->getJson(route('api.v1.public.site'))
            ->assertJsonPath('popup.title', 'PPDB Dibuka')
            ->assertJsonPath('popup.linkUrl', '/ppdb');
    }

    /** Menyeret baris di panel mengubah urutan di situs. */
    public function test_reordering_changes_the_order_on_the_site(): void
    {
        $a = Faq::factory()->create(['question' => 'Pertama?', 'sort' => 0]);
        $b = Faq::factory()->create(['question' => 'Kedua?', 'sort' => 1]);

        Livewire::test(ListFaqs::class)->call('reorderTable', [$b->getKey(), $a->getKey()]);

        $this->getJson(route('api.v1.public.site'))->assertJsonPath('faqs.0.q', 'Kedua?');
    }
}
