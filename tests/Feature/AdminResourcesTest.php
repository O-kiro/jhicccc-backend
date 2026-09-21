<?php

namespace Tests\Feature;

use App\Filament\Resources\BookLoans\Pages\ListBookLoans;
use App\Filament\Resources\Books\Pages\ListBooks;
use App\Filament\Resources\Courses\Pages\EditCourse;
use App\Filament\Resources\Courses\Pages\ListCourses;
use App\Filament\Resources\Courses\RelationManagers\EnrollmentsRelationManager;
use App\Filament\Resources\Courses\RelationManagers\ModulesRelationManager;
use App\Filament\Resources\ExamResults\ExamResultResource;
use App\Filament\Resources\ExamResults\Pages\ListExamResults;
use App\Filament\Resources\Exams\Pages\ListExams;
use App\Filament\Resources\ForumCategories\Pages\ListForumCategories;
use App\Filament\Resources\ForumThreads\ForumThreadResource;
use App\Filament\Resources\ForumThreads\Pages\EditForumThread;
use App\Filament\Resources\ForumThreads\Pages\ListForumThreads;
use App\Filament\Resources\ForumThreads\RelationManagers\RepliesRelationManager;
use App\Filament\Resources\Quotes\Pages\ListQuotes;
use App\Models\Book;
use App\Models\BookLoan;
use App\Models\Course;
use App\Models\CourseModule;
use App\Models\Enrollment;
use App\Models\Exam;
use App\Models\ExamResult;
use App\Models\ForumCategory;
use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\Quote;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Livewire\Livewire;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

/**
 * Jaring pengaman untuk panel admin.
 *
 * Halaman Filament dirender lewat Livewire, jadi memeriksanya dengan curl
 * hanya menunjukkan kerangka kosong — tabel dan relation manager belum ada di
 * HTML awal. Tes ini benar-benar memasang komponennya.
 */
class AdminResourcesTest extends TestCase
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
            'Kursus' => [ListCourses::class, Course::class],
            'Ujian' => [ListExams::class, Exam::class],
            'Hasil Ujian' => [ListExamResults::class, ExamResult::class],
            'Buku' => [ListBooks::class, Book::class],
            'Pinjaman Buku' => [ListBookLoans::class, BookLoan::class],
            'Kategori Forum' => [ListForumCategories::class, ForumCategory::class],
            'Topik Forum' => [ListForumThreads::class, ForumThread::class],
            'Kutipan Harian' => [ListQuotes::class, Quote::class],
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

    public function test_course_relation_managers_render(): void
    {
        $course = Course::factory()->create();
        CourseModule::factory()->for($course)->create(['number' => 1]);
        Enrollment::factory()->for($course)->create();

        foreach ([ModulesRelationManager::class, EnrollmentsRelationManager::class] as $manager) {
            Livewire::test($manager, [
                'ownerRecord' => $course,
                'pageClass' => EditCourse::class,
            ])->assertSuccessful();
        }
    }

    public function test_forum_replies_relation_manager_renders(): void
    {
        $thread = ForumThread::factory()->create();
        ForumReply::factory()->for($thread, 'thread')->create();

        Livewire::test(RepliesRelationManager::class, [
            'ownerRecord' => $thread,
            'pageClass' => EditForumThread::class,
        ])->assertSuccessful();
    }

    /**
     * Hasil ujian lahir dari sesi CBT yang dinilai server, dan topik forum
     * ditulis siswa. Membuat keduanya dari panel hanya akan menghasilkan data
     * tanpa asal-usul, jadi tombol tambahnya memang harus tidak ada.
     */
    public function test_records_that_only_students_can_create_cannot_be_created_from_the_panel(): void
    {
        $this->assertFalse(ExamResultResource::canCreate());
        $this->assertFalse(ForumThreadResource::canCreate());
    }
}
