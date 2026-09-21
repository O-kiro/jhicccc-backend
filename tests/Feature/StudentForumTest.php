<?php

namespace Tests\Feature;

use App\Models\ForumCategory;
use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentForumTest extends TestCase
{
    use RefreshDatabase;

    public function test_categories_carry_their_thread_counts(): void
    {
        $student = Student::factory()->create();
        $category = ForumCategory::factory()->create();

        ForumThread::factory()->count(2)->for($category, 'category')->create();

        $response = $this->actingAs($student, 'student')->getJson(route('api.v1.forum'));

        $response->assertOk()->assertJsonPath('categories.0.threads', 2);
    }

    public function test_trending_is_ordered_by_likes(): void
    {
        $student = Student::factory()->create();

        ForumThread::factory()->create(['title' => 'Sepi', 'like_count' => 3]);
        ForumThread::factory()->create(['title' => 'Ramai', 'like_count' => 99]);

        $response = $this->actingAs($student, 'student')->getJson(route('api.v1.forum'));

        $response->assertOk()->assertJsonPath('trending.0.title', 'Ramai');
    }

    /**
     * Pernah gagal dengan HTTP 500: SQLite menolak HAVING tanpa GROUP BY,
     * sehingga penyusunan daftar kontributor harus memakai has().
     */
    public function test_top_contributors_excludes_students_without_threads(): void
    {
        $student = Student::factory()->create();
        $penulis = Student::factory()->create(['name' => 'Penulis']);
        Student::factory()->create(['name' => 'Pendiam']);

        ForumThread::factory()->for($penulis)->create();

        $response = $this->actingAs($student, 'student')->getJson(route('api.v1.forum'));

        $response->assertOk()->assertJsonCount(1, 'top_contributors');
        $response->assertJsonPath('top_contributors.0.name', 'Penulis');
    }

    public function test_it_shows_five_threads_by_default_and_reports_the_total(): void
    {
        $student = Student::factory()->create();
        ForumThread::factory()->count(8)->create();

        $this->actingAs($student, 'student')
            ->getJson(route('api.v1.forum'))
            ->assertOk()
            ->assertJsonCount(5, 'threads')
            ->assertJsonPath('threads_shown', 5)
            ->assertJsonPath('threads_total', 8);
    }

    public function test_it_can_show_more_threads_on_request(): void
    {
        $student = Student::factory()->create();
        ForumThread::factory()->count(8)->create();

        $this->actingAs($student, 'student')
            ->getJson(route('api.v1.forum', ['threads' => 10]))
            ->assertOk()
            ->assertJsonCount(8, 'threads');
    }

    /** Parameter URL tidak boleh dipakai menarik seluruh isi forum sekaligus. */
    public function test_the_thread_limit_is_capped(): void
    {
        $student = Student::factory()->create();
        ForumThread::factory()->count(60)->create();

        $this->actingAs($student, 'student')
            ->getJson(route('api.v1.forum', ['threads' => 9999]))
            ->assertOk()
            ->assertJsonCount(50, 'threads');
    }

    public function test_a_student_can_open_a_new_thread(): void
    {
        $student = Student::factory()->create();
        $category = ForumCategory::factory()->create();

        $this->actingAs($student, 'student')
            ->postJson(route('api.v1.forum.threads.store'), [
                'forum_category_id' => $category->id,
                'title' => 'Bagaimana cara membagi waktu belajar?',
                'body' => 'Saya kesulitan membagi waktu antara tugas sekolah dan ekstrakurikuler.',
            ])
            ->assertCreated()
            ->assertJsonPath('title', 'Bagaimana cara membagi waktu belajar?')
            ->assertJsonPath('replies', 0);

        $this->assertDatabaseHas('forum_threads', [
            'student_id' => $student->id,
            'forum_category_id' => $category->id,
            'like_count' => 0,
        ]);
    }

    public function test_a_new_thread_needs_a_real_category_and_enough_text(): void
    {
        $student = Student::factory()->create();

        $this->actingAs($student, 'student')
            ->postJson(route('api.v1.forum.threads.store'), [
                'forum_category_id' => 9999,
                'title' => 'Pendek',
                'body' => 'Singkat.',
            ])
            ->assertUnprocessable()
            ->assertJsonValidationErrors(['forum_category_id', 'title', 'body']);
    }

    public function test_a_thread_cannot_be_posted_anonymously(): void
    {
        $category = ForumCategory::factory()->create();

        $this->postJson(route('api.v1.forum.threads.store'), [
            'forum_category_id' => $category->id,
            'title' => 'Judul yang cukup panjang untuk lolos',
            'body' => 'Isi yang cukup panjang untuk lolos validasi minimal.',
        ])->assertUnauthorized();
    }

    public function test_a_thread_can_be_opened_with_its_replies_in_order(): void
    {
        $student = Student::factory()->create();
        $thread = ForumThread::factory()->create();

        ForumReply::factory()->for($thread, 'thread')->create([
            'body' => 'Balasan kedua',
            'created_at' => now(),
        ]);
        ForumReply::factory()->for($thread, 'thread')->create([
            'body' => 'Balasan pertama',
            'created_at' => now()->subHour(),
        ]);

        $this->actingAs($student, 'student')
            ->getJson(route('api.v1.forum.threads.show', $thread))
            ->assertOk()
            ->assertJsonPath('thread.id', $thread->id)
            ->assertJsonCount(2, 'replies')
            ->assertJsonPath('replies.0.body', 'Balasan pertama')
            ->assertJsonPath('replies.1.body', 'Balasan kedua');
    }

    public function test_a_student_can_reply_to_a_thread(): void
    {
        $student = Student::factory()->create();
        $thread = ForumThread::factory()->create();

        $this->actingAs($student, 'student')
            ->postJson(route('api.v1.forum.threads.replies.store', $thread), [
                'body' => 'Menurutku bikin jadwal blok dua jam membantu.',
            ])
            ->assertCreated()
            ->assertJsonPath('is_mine', true)
            ->assertJsonPath('author', $student->name);

        $this->assertDatabaseHas('forum_replies', [
            'forum_thread_id' => $thread->id,
            'student_id' => $student->id,
        ]);
    }

    public function test_an_empty_reply_is_rejected(): void
    {
        $student = Student::factory()->create();
        $thread = ForumThread::factory()->create();

        $this->actingAs($student, 'student')
            ->postJson(route('api.v1.forum.threads.replies.store', $thread), ['body' => ''])
            ->assertUnprocessable()
            ->assertJsonValidationErrors('body');
    }

    public function test_liking_a_thread_raises_the_count_and_marks_it_liked(): void
    {
        $student = Student::factory()->create();
        $thread = ForumThread::factory()->create(['like_count' => 4]);

        $this->actingAs($student, 'student')
            ->postJson(route('api.v1.forum.threads.like', $thread))
            ->assertOk()
            ->assertJsonPath('liked', true)
            ->assertJsonPath('likes', 5);

        $this->actingAs($student, 'student')
            ->getJson(route('api.v1.forum.threads.show', $thread))
            ->assertOk()
            ->assertJsonPath('thread.liked_by_me', true);
    }

    /** Tombolnya sakelar: menekan dua kali mengembalikan keadaan semula. */
    public function test_liking_twice_removes_the_like(): void
    {
        $student = Student::factory()->create();
        $thread = ForumThread::factory()->create(['like_count' => 4]);

        $this->actingAs($student, 'student')
            ->postJson(route('api.v1.forum.threads.like', $thread))->assertOk();

        $this->actingAs($student, 'student')
            ->postJson(route('api.v1.forum.threads.like', $thread))
            ->assertOk()
            ->assertJsonPath('liked', false)
            ->assertJsonPath('likes', 4);

        $this->assertSame(0, $thread->likes()->count());
    }

    /** Batasan unik mencegah satu siswa menyukai topik yang sama dua kali. */
    public function test_a_student_cannot_like_the_same_thread_twice(): void
    {
        $student = Student::factory()->create();
        $other = Student::factory()->create();
        $thread = ForumThread::factory()->create(['like_count' => 0]);

        $this->actingAs($student, 'student')
            ->postJson(route('api.v1.forum.threads.like', $thread))->assertOk();
        $this->actingAs($other, 'student')
            ->postJson(route('api.v1.forum.threads.like', $thread))->assertOk();

        $this->assertSame(2, $thread->likes()->count());
        $this->assertSame(2, $thread->refresh()->like_count);
    }

    public function test_the_like_state_is_per_student(): void
    {
        $student = Student::factory()->create();
        $other = Student::factory()->create();
        $thread = ForumThread::factory()->create();

        $this->actingAs($other, 'student')
            ->postJson(route('api.v1.forum.threads.like', $thread))->assertOk();

        $this->actingAs($student, 'student')
            ->getJson(route('api.v1.forum.threads.show', $thread))
            ->assertOk()
            ->assertJsonPath('thread.liked_by_me', false);
    }

    public function test_replying_and_liking_need_authentication(): void
    {
        $thread = ForumThread::factory()->create();

        $this->postJson(route('api.v1.forum.threads.replies.store', $thread), ['body' => 'halo'])
            ->assertUnauthorized();
        $this->postJson(route('api.v1.forum.threads.like', $thread))->assertUnauthorized();
        $this->getJson(route('api.v1.forum.threads.show', $thread))->assertUnauthorized();
    }

    public function test_stats_count_threads_and_replies(): void
    {
        $student = Student::factory()->create();
        $thread = ForumThread::factory()->create();
        ForumReply::factory()->count(3)->for($thread, 'thread')->create();

        $response = $this->actingAs($student, 'student')->getJson(route('api.v1.forum'));

        $response->assertOk();

        $stats = collect($response->json('stats'))->pluck('value', 'label');
        $this->assertSame('1', $stats['Total Topics']);
        $this->assertSame('3', $stats['Total Replies']);
    }
}
