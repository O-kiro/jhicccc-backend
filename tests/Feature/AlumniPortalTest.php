<?php

namespace Tests\Feature;

use App\Models\AlumniAccount;
use App\Models\AlumniForumCategory;
use App\Models\AlumniForumReply;
use App\Models\AlumniForumThread;
use App\Models\AlumniOutcome;
use App\Models\Scholarship;
use App\Models\Student;
use App\Models\Teacher;
use Database\Seeders\AlumniSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class AlumniPortalTest extends TestCase
{
    use RefreshDatabase;

    public function test_an_alumnus_logs_in_with_email(): void
    {
        $alumni = AlumniAccount::factory()->create([
            'email' => 'alumni@madrasah.test',
            'password' => 'rahasia123',
            'graduation_year' => 2019,
        ]);

        $res = $this->postJson(route('api.v1.login'), [
            'identifier' => 'alumni@madrasah.test',
            'password' => 'rahasia123',
        ])->assertOk()
            ->assertJsonPath('role', 'alumni')
            ->assertJsonPath('alumni.angkatan', 'Alumni 2019');

        $this->withToken($res->json('token'))
            ->getJson(route('api.v1.alumni.me'))
            ->assertOk()
            ->assertJsonPath('id', $alumni->id);
    }

    public function test_an_alumnus_without_a_password_or_inactive_cannot_log_in(): void
    {
        AlumniAccount::factory()->tanpaAksesPortal()->create(['email' => 'baru@madrasah.test']);
        AlumniAccount::factory()->inactive()->create(['email' => 'cuti@madrasah.test', 'password' => 'rahasia123']);

        $this->postJson(route('api.v1.login'), ['identifier' => 'baru@madrasah.test', 'password' => 'apa-saja'])
            ->assertStatus(422)
            ->assertJsonPath('errors.identifier.0', 'NISN/NIP/Email atau kata sandi salah.');

        $this->postJson(route('api.v1.login'), ['identifier' => 'cuti@madrasah.test', 'password' => 'rahasia123'])
            ->assertStatus(422)
            ->assertJsonPath('errors.identifier.0', 'Akun ini tidak aktif. Hubungi admin madrasah.');
    }

    public function test_each_portal_only_accepts_its_own_token(): void
    {
        $alumni = AlumniAccount::factory()->create()->createToken('uji')->plainTextToken;
        $siswa = Student::factory()->create()->createToken('uji')->plainTextToken;
        $guru = Teacher::factory()->create()->createToken('uji')->plainTextToken;

        $this->withToken($alumni)->getJson(route('api.v1.overview'))->assertUnauthorized();
        $this->app['auth']->forgetGuards();
        $this->withToken($alumni)->getJson(route('api.v1.guru.overview'))->assertUnauthorized();
        $this->app['auth']->forgetGuards();
        // Perpustakaan hanya untuk siswa dan guru.
        $this->withToken($alumni)->getJson(route('api.v1.library'))->assertUnauthorized();
        $this->app['auth']->forgetGuards();
        $this->withToken($siswa)->getJson(route('api.v1.alumni.overview'))->assertUnauthorized();
        $this->app['auth']->forgetGuards();
        $this->withToken($guru)->getJson(route('api.v1.alumni.forum'))->assertUnauthorized();
        $this->app['auth']->forgetGuards();

        $this->withToken($alumni)->getJson(route('api.v1.alumni.overview'))->assertOk();
    }

    public function test_the_overview_counts_come_from_the_data(): void
    {
        $alumni = AlumniAccount::factory()->create();
        AlumniAccount::factory()->count(2)->create();
        AlumniAccount::factory()->inactive()->create();

        AlumniOutcome::factory()->create(['year' => 2026, 'category' => 'ptn', 'students' => 192]);
        AlumniOutcome::factory()->create(['year' => 2026, 'category' => 'kedinasan', 'students' => 48]);
        AlumniOutcome::factory()->create(['year' => 2026, 'category' => 'kerja', 'students' => 80]);
        // Tahun lama tidak boleh ikut terhitung di beranda.
        AlumniOutcome::factory()->create(['year' => 2020, 'category' => 'ptn', 'students' => 999]);

        Scholarship::factory()->count(2)->create(['quota' => 25]);
        Scholarship::factory()->ditutup()->create();
        // Ditulis alumni yang sama supaya jumlah anggota tidak ikut bertambah.
        AlumniForumThread::factory()->count(3)->create(['alumni_account_id' => $alumni->id]);

        $this->actingAs($alumni, 'alumni')
            ->getJson(route('api.v1.alumni.overview'))
            ->assertOk()
            ->assertJsonPath('summary.tahun', 2026)
            ->assertJsonPath('summary.alumni_terdata', 320)
            ->assertJsonPath('summary.lanjut_studi_negeri', 240)
            // PHP menulis pecahan bulat tanpa koma: 75.0 jadi 75 di JSON.
            ->assertJsonPath('summary.lanjut_studi_negeri_persen', 75)
            ->assertJsonPath('summary.program_beasiswa', 2)
            ->assertJsonPath('summary.kuota_beasiswa', 50)
            ->assertJsonPath('summary.topik_forum', 3)
            ->assertJsonPath('summary.anggota_forum', 3);
    }

    public function test_the_scholarship_summary_ignores_the_category_filter(): void
    {
        $alumni = AlumniAccount::factory()->create();
        Scholarship::factory()->create(['category' => 'Sains & Teknologi', 'quota' => 25, 'verified' => 10, 'applicants' => 40]);
        Scholarship::factory()->create(['category' => 'Olimpiade Sains', 'quota' => 15, 'verified' => 12, 'applicants' => 30]);
        Scholarship::factory()->ditutup()->create(['category' => 'Seni & Humaniora', 'applicants' => 5]);

        $res = $this->actingAs($alumni, 'alumni')
            ->getJson(route('api.v1.alumni.beasiswa', ['kategori' => 'Olimpiade Sains']))
            ->assertOk()
            ->assertJsonCount(1, 'scholarships')
            ->assertJsonPath('scholarships.0.category', 'Olimpiade Sains')
            ->assertJsonPath('selected', 'Olimpiade Sains');

        // Ringkasan tetap menghitung seluruh katalog.
        $res->assertJsonPath('summary.program_aktif', 2)
            ->assertJsonPath('summary.program_total', 3)
            ->assertJsonPath('summary.kuota', 40)
            ->assertJsonPath('summary.pendaftar', 75)
            ->assertJsonPath('summary.terverifikasi', 22)
            ->assertJsonPath('summary.penyerapan', 55)
            ->assertJsonPath('summary.sisa_kuota', 18);
    }

    public function test_the_distribution_computes_percentages_and_filters_by_year(): void
    {
        $alumni = AlumniAccount::factory()->create();
        AlumniOutcome::factory()->create(['year' => 2026, 'category' => 'ptn', 'students' => 192, 'sort' => 0]);
        AlumniOutcome::factory()->create(['year' => 2026, 'category' => 'pts', 'students' => 58, 'sort' => 1]);
        AlumniOutcome::factory()->create(['year' => 2026, 'category' => 'kedinasan', 'students' => 48, 'sort' => 2]);
        AlumniOutcome::factory()->create(['year' => 2026, 'category' => 'kerja', 'students' => 22, 'sort' => 3]);
        AlumniOutcome::factory()->create(['year' => 2025, 'category' => 'ptn', 'students' => 100]);

        // Tanpa parameter: tahun terbaru.
        $this->actingAs($alumni, 'alumni')
            ->getJson(route('api.v1.alumni.statistik'))
            ->assertOk()
            ->assertJsonPath('year', 2026)
            ->assertJsonPath('total', 320)
            ->assertJsonPath('outcomes.0.label', 'Lolos PTN')
            ->assertJsonPath('outcomes.0.percent', 60)
            ->assertJsonPath('outcomes.3.percent', 6.9)
            ->assertJsonPath('years', [2026, 2025]);

        $this->actingAs($alumni, 'alumni')
            ->getJson(route('api.v1.alumni.statistik', ['tahun' => 2025]))
            ->assertJsonPath('year', 2025)
            ->assertJsonPath('total', 100);

        // Tahun yang tidak ada jatuh ke tahun terbaru, bukan halaman kosong.
        $this->actingAs($alumni, 'alumni')
            ->getJson(route('api.v1.alumni.statistik', ['tahun' => 1999]))
            ->assertJsonPath('year', 2026);
    }

    public function test_an_alumnus_opens_a_topic_replies_and_likes_it(): void
    {
        $alumni = AlumniAccount::factory()->create(['graduation_year' => 2019]);
        $kategori = AlumniForumCategory::factory()->create();

        $topik = $this->actingAs($alumni, 'alumni')
            ->postJson(route('api.v1.alumni.forum.threads.store'), [
                'alumni_forum_category_id' => $kategori->id,
                'title' => 'Tips lolos seleksi kerja di BUMN',
                'body' => str_repeat('Berbagi pengalaman seleksi. ', 3),
            ])->assertCreated()
            ->assertJsonPath('author_note', 'Alumni 2019')
            ->json('id');

        $balasan = $this->actingAs($alumni, 'alumni')
            ->postJson(route('api.v1.alumni.forum.threads.replies.store', $topik), ['body' => 'Balasan pertama'])
            ->assertCreated()
            ->json('id');

        // Balasan atas balasan ditarik ke induk teratas: satu tingkat saja.
        $cucu = $this->actingAs($alumni, 'alumni')
            ->postJson(route('api.v1.alumni.forum.threads.replies.store', $topik), [
                'body' => 'Balasan atas balasan',
                'parent_id' => $balasan,
            ])->assertCreated()->json('parent_id');
        $this->assertSame($balasan, $cucu);

        $this->actingAs($alumni, 'alumni')
            ->postJson(route('api.v1.alumni.forum.threads.like', $topik))
            ->assertOk()
            ->assertJsonPath('liked', true)
            ->assertJsonPath('likes', 1);

        $this->actingAs($alumni, 'alumni')
            ->postJson(route('api.v1.alumni.forum.threads.like', $topik))
            ->assertJsonPath('liked', false)
            ->assertJsonPath('likes', 0);

        $this->actingAs($alumni, 'alumni')
            ->getJson(route('api.v1.alumni.forum.threads.show', $topik))
            ->assertOk()
            ->assertJsonCount(1, 'replies')
            ->assertJsonCount(1, 'replies.0.children')
            ->assertJsonPath('thread.replies', 2)
            ->assertJsonPath('replies.0.is_mine', true);
    }

    public function test_an_alumnus_can_only_delete_their_own_reply(): void
    {
        $alumni = AlumniAccount::factory()->create();
        $milikku = AlumniForumReply::factory()->create(['alumni_account_id' => $alumni->id]);
        $milikOrangLain = AlumniForumReply::factory()->create();

        $this->actingAs($alumni, 'alumni')
            ->deleteJson(route('api.v1.alumni.forum.replies.destroy', $milikOrangLain))
            ->assertNotFound();

        $this->actingAs($alumni, 'alumni')
            ->deleteJson(route('api.v1.alumni.forum.replies.destroy', $milikku))
            ->assertOk();

        $this->assertSoftDeleted($milikku);
    }

    public function test_the_forum_list_can_be_sorted_by_popularity(): void
    {
        $alumni = AlumniAccount::factory()->create();
        AlumniForumThread::factory()->create(['title' => 'Sepi', 'like_count' => 1]);
        AlumniForumThread::factory()->create(['title' => 'Ramai', 'like_count' => 99]);

        $this->actingAs($alumni, 'alumni')
            ->getJson(route('api.v1.alumni.forum', ['urut' => 'populer']))
            ->assertOk()
            ->assertJsonPath('sort', 'populer')
            ->assertJsonPath('threads.0.title', 'Ramai');

        $this->actingAs($alumni, 'alumni')
            ->getJson(route('api.v1.alumni.forum'))
            ->assertJsonPath('sort', 'terbaru');
    }

    public function test_the_forum_can_be_searched(): void
    {
        $alumni = AlumniAccount::factory()->create();
        AlumniForumThread::factory()->create(['title' => 'Tips wawancara kerja', 'body' => 'isi apa saja']);
        AlumniForumThread::factory()->create(['title' => 'Reuni akbar', 'body' => 'isi apa saja']);

        $this->actingAs($alumni, 'alumni')
            ->getJson(route('api.v1.alumni.forum', ['q' => 'wawancara']))
            ->assertOk()
            ->assertJsonCount(1, 'threads')
            ->assertJsonPath('threads.0.title', 'Tips wawancara kerja')
            ->assertJsonPath('query', 'wawancara')
            // Totalnya ikut menyempit supaya tombol "muat lainnya" tidak muncul.
            ->assertJsonPath('threads_total', 1);
    }

    public function test_an_alumnus_can_change_their_password_and_other_sessions_end(): void
    {
        $alumni = AlumniAccount::factory()->create(['password' => 'lama12345']);
        $alumni->createToken('perangkat-lama');
        $token = $alumni->createToken('perangkat-ini')->plainTextToken;

        $this->withToken($token)
            ->postJson(route('api.v1.me.password'), [
                'current_password' => 'lama12345',
                'password' => 'baru12345',
                'password_confirmation' => 'baru12345',
            ])->assertOk()
            ->assertJsonPath('other_sessions_revoked', 1);
    }

    /** Seeder dijalankan ulang tiap `docker compose up`; tidak boleh menggandakan. */
    public function test_the_alumni_seeder_can_run_twice_without_duplicating(): void
    {
        $this->seed(AlumniSeeder::class);
        $hitung = fn (): array => [
            AlumniAccount::query()->count(),
            Scholarship::query()->count(),
            AlumniOutcome::query()->count(),
            AlumniForumCategory::query()->count(),
            AlumniForumThread::query()->count(),
            AlumniForumReply::query()->count(),
        ];
        $sebelum = $hitung();

        $this->seed(AlumniSeeder::class);

        $this->assertSame($sebelum, $hitung());
        $this->assertNotContains(0, $sebelum);
    }
}
