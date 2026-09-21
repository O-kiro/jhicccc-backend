<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ForumCategoryResource;
use App\Http\Resources\V1\ForumThreadResource;
use App\Models\ForumCategory;
use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ForumController extends Controller
{
    private const THREADS_DEFAULT = 5;

    private const THREADS_MAX = 50;

    public function __invoke(Request $request): JsonResponse
    {
        $categories = ForumCategory::query()->withCount('threads')->orderBy('id')->get();

        // Dibatasi supaya "Muat Diskusi Lainnya" tidak bisa dipakai menarik
        // seluruh isi forum dalam satu permintaan lewat parameter URL.
        $perluTampil = (int) $request->integer('threads', self::THREADS_DEFAULT);
        $perluTampil = max(self::THREADS_DEFAULT, min($perluTampil, self::THREADS_MAX));

        $totalThreads = ForumThread::query()->count();

        /** @var Student $student */
        $student = $request->user();

        $threads = ForumThread::query()
            // Suka dibatasi ke siswa ini: yang dipakai kartu hanya "sudah
            // suka atau belum", bukan seluruh daftar penyuka.
            ->with(['category', 'student', 'likes' => fn ($q) => $q->where('student_id', $student->id)])
            ->withCount('replies')
            ->latest('created_at')
            ->limit($perluTampil)
            ->get();

        return response()->json([
            'categories' => ForumCategoryResource::collection($categories),
            'threads' => ForumThreadResource::collection($threads),
            // Dipakai portal untuk menyembunyikan tombol "Muat Diskusi
            // Lainnya" ketika memang sudah habis.
            'threads_shown' => $threads->count(),
            'threads_total' => $totalThreads,
            'stats' => [
                ['value' => (string) Student::query()->count(), 'label' => 'Active Members'],
                ['value' => (string) $totalThreads, 'label' => 'Total Topics'],
                ['value' => (string) ForumReply::query()->count(), 'label' => 'Total Replies'],
                [
                    'value' => (string) ForumThread::query()->where('created_at', '>=', now()->startOfDay())->count(),
                    'label' => 'New Today',
                ],
            ],
            // "Trending" = paling banyak disukai, bukan daftar terpisah yang
            // harus diurus manual.
            'trending' => ForumThread::query()
                ->with('category')
                ->orderByDesc('like_count')
                ->limit(3)
                ->get()
                ->map(fn (ForumThread $t): array => [
                    'tag' => $t->category->name,
                    'title' => $t->title,
                ]),
            // has(), bukan having(): SQLite menolak HAVING tanpa GROUP BY,
            // sedangkan has() dikompilasi jadi "where exists (...)".
            'top_contributors' => Student::query()
                ->withCount('forumThreads')
                ->has('forumThreads')
                ->orderByDesc('forum_threads_count')
                ->limit(3)
                ->get()
                ->map(fn (Student $s): array => [
                    'name' => $s->name,
                    'posts' => $s->forum_threads_count,
                ]),
        ]);
    }
}
