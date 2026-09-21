<?php

namespace Tests\Feature;

use App\Models\Faq;
use App\Models\NewsPost;
use App\Models\Student;
use App\Services\RevalidasiSitus;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\Request;
use Illuminate\Support\Facades\Http;
use Tests\TestCase;

class SiteRevalidationTest extends TestCase
{
    use RefreshDatabase;

    private const URL = 'http://situs.test/api/revalidate';

    protected function setUp(): void
    {
        parent::setUp();

        config([
            'services.situs.revalidate_url' => self::URL,
            'services.situs.revalidate_secret' => 'rahasia-uji',
        ]);
    }

    public function test_saving_cms_content_notifies_the_site_once_with_the_secret(): void
    {
        Http::fake([self::URL => Http::response(['revalidated' => true])]);

        NewsPost::factory()->create();
        $this->app->terminate();

        Http::assertSentCount(1);
        Http::assertSent(fn (Request $r) => $r->url() === self::URL
            && $r->hasHeader('x-revalidate-secret', 'rahasia-uji'));
    }

    /** Menyeret urutan sepuluh baris sekaligus cukup satu panggilan. */
    public function test_many_changes_in_one_request_make_one_call(): void
    {
        Http::fake([self::URL => Http::response(['revalidated' => true])]);

        Faq::factory()->count(10)->create();
        Faq::query()->each(fn (Faq $f) => $f->update(['sort' => $f->sort + 1]));
        $this->app->terminate();

        Http::assertSentCount(1);
    }

    public function test_deleting_cms_content_also_notifies_the_site(): void
    {
        Http::fake([self::URL => Http::response(['revalidated' => true])]);
        $faq = Faq::factory()->create();
        $this->app->terminate();

        $faq->delete();
        $this->app->terminate();

        Http::assertSentCount(2);
    }

    public function test_non_cms_models_do_not_notify_the_site(): void
    {
        Http::fake();

        Student::factory()->create();
        $this->app->terminate();

        Http::assertNothingSent();
    }

    public function test_nothing_is_sent_when_not_configured(): void
    {
        config(['services.situs.revalidate_secret' => null]);
        Http::fake();

        NewsPost::factory()->create();
        $this->app->terminate();

        Http::assertNothingSent();
    }

    /** Situs mati tidak boleh menggagalkan penyimpanan di panel admin. */
    public function test_a_site_that_is_down_never_breaks_saving(): void
    {
        Http::fake(fn () => throw new ConnectionException('situs mati'));

        $post = NewsPost::factory()->create();
        $this->app->terminate();

        $this->assertModelExists($post);
        $this->assertFalse(app(RevalidasiSitus::class)->kirim());
    }

    public function test_a_rejected_call_is_reported_not_thrown(): void
    {
        Http::fake([self::URL => Http::response(['message' => 'Tidak diizinkan.'], 401)]);

        $this->assertFalse(app(RevalidasiSitus::class)->kirim());
    }
}
