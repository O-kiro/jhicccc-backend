<?php

namespace Tests\Feature;

use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\Student;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StudentForumThreadingTest extends TestCase
{
    use RefreshDatabase;

    private function balas(Student $siswa, ForumThread $thread, string $isi, ?int $induk = null)
    {
        return $this->actingAs($siswa, 'student')->postJson(
            route('api.v1.forum.threads.replies.store', $thread),
            array_filter(['body' => $isi, 'parent_id' => $induk]),
        );
    }

    public function test_a_reply_to_a_reply_is_nested_under_it(): void
    {
        $siswa = Student::factory()->create();
        $thread = ForumThread::factory()->create();

        $induk = $this->balas($siswa, $thread, 'Balasan induk')->json('id');
        $this->balas($siswa, $thread, 'Balasan anak', $induk)->assertCreated()->assertJsonPath('parent_id', $induk);

        $this->actingAs($siswa, 'student')
            ->getJson(route('api.v1.forum.threads.show', $thread))
            ->assertOk()
            ->assertJsonCount(1, 'replies')
            ->assertJsonPath('replies.0.body', 'Balasan induk')
            ->assertJsonPath('replies.0.children.0.body', 'Balasan anak');
    }

    /** Satu tingkat saja: membalas balasan-anak menempel ke induk tingkat atas. */
    public function test_replying_to_a_nested_reply_attaches_to_its_top_level_parent(): void
    {
        $siswa = Student::factory()->create();
        $thread = ForumThread::factory()->create();

        $induk = $this->balas($siswa, $thread, 'Induk')->json('id');
        $anak = $this->balas($siswa, $thread, 'Anak', $induk)->json('id');

        $this->balas($siswa, $thread, 'Cucu', $anak)->assertCreated()->assertJsonPath('parent_id', $induk);
    }

    public function test_the_parent_must_belong_to_the_same_thread(): void
    {
        $siswa = Student::factory()->create();
        $lain = ForumReply::factory()->create();

        $this->balas($siswa, ForumThread::factory()->create(), 'Salah topik', $lain->id)
            ->assertUnprocessable()
            ->assertJsonValidationErrors('parent_id');
    }

    public function test_a_student_can_delete_their_own_reply(): void
    {
        $siswa = Student::factory()->create();
        $reply = ForumReply::factory()->for($siswa)->create();

        $this->actingAs($siswa, 'student')
            ->deleteJson(route('api.v1.forum.replies.destroy', $reply))
            ->assertOk();

        $this->assertSoftDeleted($reply);
    }

    public function test_a_student_cannot_delete_someone_elses_reply(): void
    {
        $reply = ForumReply::factory()->create();

        $this->actingAs(Student::factory()->create(), 'student')
            ->deleteJson(route('api.v1.forum.replies.destroy', $reply))
            ->assertForbidden();

        $this->assertNotSoftDeleted($reply);
    }

    /**
     * Menghapus balasan sendiri tidak boleh ikut menghapus balasan orang lain
     * yang menanggapinya — yang tersisa hanya penanda tanpa isi.
     */
    public function test_a_deleted_reply_with_children_stays_as_a_tombstone(): void
    {
        $penulis = Student::factory()->create();
        $lain = Student::factory()->create();
        $thread = ForumThread::factory()->create();

        $induk = $this->balas($penulis, $thread, 'Akan dihapus')->json('id');
        $this->balas($lain, $thread, 'Tanggapan orang lain', $induk);

        $this->actingAs($penulis, 'student')->deleteJson(route('api.v1.forum.replies.destroy', $induk))->assertOk();

        $this->actingAs($lain, 'student')
            ->getJson(route('api.v1.forum.threads.show', $thread))
            ->assertJsonCount(1, 'replies')
            ->assertJsonPath('replies.0.is_deleted', true)
            ->assertJsonPath('replies.0.body', null)
            ->assertJsonPath('replies.0.author', null)
            ->assertJsonPath('replies.0.children.0.body', 'Tanggapan orang lain');
    }

    public function test_a_deleted_reply_without_children_disappears(): void
    {
        $siswa = Student::factory()->create();
        $thread = ForumThread::factory()->create();
        $id = $this->balas($siswa, $thread, 'Hilang saja')->json('id');

        $this->actingAs($siswa, 'student')->deleteJson(route('api.v1.forum.replies.destroy', $id));

        $this->actingAs($siswa, 'student')
            ->getJson(route('api.v1.forum.threads.show', $thread))
            ->assertJsonCount(0, 'replies')
            ->assertJsonPath('thread.replies', 0);
    }

    public function test_a_deleted_reply_cannot_be_replied_to(): void
    {
        $siswa = Student::factory()->create();
        $thread = ForumThread::factory()->create();
        $id = $this->balas($siswa, $thread, 'Dihapus')->json('id');
        $this->actingAs($siswa, 'student')->deleteJson(route('api.v1.forum.replies.destroy', $id));

        $this->balas($siswa, $thread, 'Terlambat', $id)->assertUnprocessable();
    }
}
