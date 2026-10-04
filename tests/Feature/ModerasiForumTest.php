<?php

namespace Tests\Feature;

use App\Filament\Resources\ModerasiForum\Pages\ListForumModerations;
use App\Models\AlumniAccount;
use App\Models\AlumniForumCategory;
use App\Models\ForumCategory;
use App\Models\ForumModeration;
use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\Student;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Http;
use Livewire\Livewire;
use Tests\TestCase;

class ModerasiForumTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        config(['moderasi.gemini.key' => 'kunci-uji']);
    }

    private function geminiMenjawab(bool $melanggar, string $alasan = ''): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response([
            'candidates' => [['content' => ['parts' => [['text' => json_encode(compact('melanggar', 'alasan'))]]]]],
        ])]);
    }

    private function kirimTopikSiswa(Student $siswa)
    {
        return $this->actingAs($siswa, 'student')->postJson(route('api.v1.forum.threads.store'), [
            'forum_category_id' => ForumCategory::factory()->create()->id,
            'title' => 'Judul topik yang cukup panjang',
            'body' => 'Isi topik yang panjangnya lebih dari dua puluh karakter.',
        ]);
    }

    public function test_topik_yang_lolos_langsung_tayang_tanpa_catatan(): void
    {
        $this->geminiMenjawab(false);

        $this->kirimTopikSiswa(Student::factory()->create())->assertCreated();

        $this->assertSame(1, ForumThread::query()->count());
        $this->assertSame(0, ForumModeration::query()->count());
        Http::assertSent(fn ($r) => $r->hasHeader('x-goog-api-key', 'kunci-uji'));
    }

    public function test_topik_yang_melanggar_ditolak_dan_dicatat(): void
    {
        $this->geminiMenjawab(true, 'Mengandung makian.');

        $this->kirimTopikSiswa(Student::factory()->create())
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['body' => 'Mengandung makian.']);

        $this->assertSame(0, ForumThread::query()->count());
        $this->assertDatabaseHas('forum_moderations', [
            'forum' => 'siswa', 'jenis' => 'topik', 'status' => 'ditolak', 'alasan' => 'Mengandung makian.',
        ]);
    }

    public function test_gemini_error_postingan_tetap_tayang_dan_ditandai_belum_dicek(): void
    {
        Http::fake(['generativelanguage.googleapis.com/*' => Http::response('kuota habis', 429)]);

        $this->kirimTopikSiswa(Student::factory()->create())->assertCreated();

        $thread = ForumThread::query()->sole();
        $this->assertDatabaseHas('forum_moderations', [
            'status' => 'belum_dicek', 'postingan_type' => $thread->getMorphClass(), 'postingan_id' => $thread->id,
        ]);
    }

    public function test_tanpa_api_key_moderasi_mati(): void
    {
        config(['moderasi.gemini.key' => null]);
        Http::fake();

        $this->kirimTopikSiswa(Student::factory()->create())->assertCreated();

        Http::assertNothingSent();
        $this->assertSame(0, ForumModeration::query()->count());
    }

    public function test_balasan_alumni_yang_melanggar_ditolak(): void
    {
        $this->geminiMenjawab(true, 'Spam iklan.');
        $alumni = AlumniAccount::factory()->create();
        $kategori = AlumniForumCategory::factory()->create();

        $topik = $this->actingAs($alumni, 'alumni')->postJson(route('api.v1.alumni.forum.threads.store'), [
            'alumni_forum_category_id' => $kategori->id,
            'title' => 'Judul topik alumni yang panjang',
            'body' => 'Isi topik alumni lebih dari dua puluh karakter.',
        ]);
        $topik->assertUnprocessable();

        $this->assertDatabaseHas('forum_moderations', ['forum' => 'alumni', 'jenis' => 'topik', 'status' => 'ditolak']);
    }

    public function test_admin_bisa_memulihkan_balasan_yang_salah_tolak(): void
    {
        $this->geminiMenjawab(true, 'Salah tangkap.');
        $thread = ForumThread::factory()->create();

        $this->actingAs(Student::factory()->create(), 'student')
            ->postJson(route('api.v1.forum.threads.replies.store', $thread), ['body' => 'Mantap jiwa, gas terus!'])
            ->assertUnprocessable();

        $catatan = ForumModeration::query()->sole();

        $this->actingAs(User::factory()->create());
        Livewire::test(ListForumModerations::class)
            ->callTableAction('pulihkan', $catatan)
            ->assertHasNoTableActionErrors();

        $balasan = ForumReply::query()->sole();
        $this->assertSame('Mantap jiwa, gas terus!', $balasan->body);
        $this->assertSame($thread->id, $balasan->forum_thread_id);
        $this->assertSame('dipulihkan', $catatan->fresh()->status);
    }
}
