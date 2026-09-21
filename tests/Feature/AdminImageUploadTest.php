<?php

namespace Tests\Feature;

use App\Filament\Resources\GalleryItems\Pages\EditGalleryItem;
use App\Filament\Resources\NewsPosts\Pages\CreateNewsPost;
use App\Filament\Resources\NewsPosts\Pages\EditNewsPost;
use App\Models\GalleryItem;
use App\Models\NewsPost;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Livewire\Livewire;
use Tests\TestCase;

class AdminImageUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
        $this->actingAs(User::factory()->create());
    }

    public function test_an_uploaded_news_image_reaches_the_site_as_a_storage_path(): void
    {
        Livewire::test(CreateNewsPost::class)
            ->fillForm([
                'title' => 'Berita Bergambar',
                'slug' => 'berita-bergambar',
                'category' => 'Pengumuman',
                'published_on' => now()->toDateString(),
                'author' => 'Humas',
                'tone' => 'teal',
                'excerpt' => 'Ringkasan.',
                'content' => [['paragraf' => 'Isi.']],
                'image_path' => UploadedFile::fake()->image('foto.jpg', 800, 450),
                'is_published' => true,
            ])
            ->call('create')
            ->assertHasNoFormErrors();

        $post = NewsPost::query()->sole();
        Storage::disk('public')->assertExists($post->image_path);
        $this->assertStringStartsWith('situs/berita/', $post->image_path);

        $this->getJson(route('api.v1.public.site'))
            ->assertJsonPath('news.0.image', '/storage/'.$post->image_path);
    }

    /**
     * Jalur lama (/photos/...) bukan berkas di penyimpanan Laravel. Membuka
     * dan menyimpan berita tanpa mengunggah apa pun tidak boleh menghapusnya.
     */
    public function test_saving_without_uploading_keeps_the_existing_photo_path(): void
    {
        $post = NewsPost::factory()->create(['image' => '/photos/lama.jpg', 'image_path' => null]);

        Livewire::test(EditNewsPost::class, ['record' => $post->getKey()])
            ->call('save')
            ->assertHasNoFormErrors();

        $this->assertSame('/photos/lama.jpg', $post->refresh()->image);
        $this->getJson(route('api.v1.public.site'))->assertJsonPath('news.0.image', '/photos/lama.jpg');
    }

    public function test_an_upload_wins_over_the_old_path(): void
    {
        $post = NewsPost::factory()->create(['image' => '/photos/lama.jpg', 'image_path' => 'situs/berita/baru.jpg']);

        $this->assertSame('/storage/situs/berita/baru.jpg', $post->publicImage());
    }

    public function test_replacing_an_upload_deletes_the_old_file(): void
    {
        Storage::disk('public')->put('situs/galeri/lama.jpg', 'x');
        $item = GalleryItem::factory()->create(['image_path' => 'situs/galeri/lama.jpg']);

        $item->update(['image_path' => 'situs/galeri/baru.jpg']);

        Storage::disk('public')->assertMissing('situs/galeri/lama.jpg');
    }

    public function test_deleting_content_deletes_its_uploaded_file(): void
    {
        Storage::disk('public')->put('situs/galeri/hapus.jpg', 'x');
        $item = GalleryItem::factory()->create(['image_path' => 'situs/galeri/hapus.jpg']);

        $item->delete();

        Storage::disk('public')->assertMissing('situs/galeri/hapus.jpg');
    }

    public function test_a_non_image_file_is_rejected(): void
    {
        $item = GalleryItem::factory()->create();

        Livewire::test(EditGalleryItem::class, ['record' => $item->getKey()])
            ->fillForm(['image_path' => UploadedFile::fake()->create('virus.pdf', 10, 'application/pdf')])
            ->call('save')
            ->assertHasFormErrors(['image_path']);
    }

    public function test_an_oversized_image_is_rejected(): void
    {
        $item = GalleryItem::factory()->create();

        Livewire::test(EditGalleryItem::class, ['record' => $item->getKey()])
            ->fillForm(['image_path' => UploadedFile::fake()->image('besar.jpg')->size(3000)])
            ->call('save')
            ->assertHasFormErrors(['image_path']);
    }
}
