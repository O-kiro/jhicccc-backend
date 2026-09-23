<?php

namespace App\Http\Controllers\Api\V1\Alumni;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Alumni\KategoriResource;
use App\Http\Resources\V1\Alumni\TopikResource;
use App\Models\AlumniAccount;
use App\Models\AlumniForumCategory;
use App\Models\AlumniForumReply;
use App\Models\AlumniForumThread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

/**
 * Forum Alumni. Bentuk responsnya sengaja sama dengan forum siswa supaya
 * komponen portalnya seragam — tapi datanya tabel sendiri, jadi diskusi siswa
 * dan alumni tidak pernah tercampur.
 */
class ForumController extends Controller
{
    private const THREADS_DEFAULT = 5;

    private const THREADS_MAX = 50;

    public function __invoke(Request $request): JsonResponse
    {
        /** @var AlumniAccount $alumni */
        $alumni = $request->user();

        $categories = AlumniForumCategory::query()
            ->withCount('threads')
            ->orderBy('sort')
            ->orderBy('id')
            ->get();

        // Dibatasi supaya "Muat Diskusi Lainnya" tidak bisa dipakai menarik
        // seluruh isi forum dalam satu permintaan lewat parameter URL.
        $perluTampil = (int) $request->integer('threads', self::THREADS_DEFAULT);
        $perluTampil = max(self::THREADS_DEFAULT, min($perluTampil, self::THREADS_MAX));

        $urut = $request->query('urut') === 'populer' ? 'populer' : 'terbaru';
        $cari = trim((string) $request->query('q'));
        $totalThreads = AlumniForumThread::query()->count();

        $cocok = fn ($q) => $q->when($cari !== '', fn ($w) => $w->where(fn ($x) => $x
            ->where('title', 'like', "%{$cari}%")
            ->orWhere('body', 'like', "%{$cari}%")));

        $threads = AlumniForumThread::query()
            ->with([
                'category',
                'author',
                'likes' => fn ($q) => $q->where('alumni_account_id', $alumni->id),
            ])
            ->withCount('replies')
            ->tap($cocok)
            ->when($urut === 'populer',
                fn ($q) => $q->orderByDesc('like_count')->orderByDesc('created_at'),
                fn ($q) => $q->latest('created_at'),
            )
            ->limit($perluTampil)
            ->get();

        return response()->json([
            'categories' => KategoriResource::collection($categories),
            'threads' => TopikResource::collection($threads),
            'sort' => $urut,
            'query' => $cari !== '' ? $cari : null,
            'threads_shown' => $threads->count(),
            // Saat mencari, "total" adalah jumlah yang cocok — kalau tidak,
            // tombol "Muat Diskusi Lainnya" muncul padahal sudah habis.
            'threads_total' => $cari === ''
                ? $totalThreads
                : AlumniForumThread::query()->tap($cocok)->count(),
            'stats' => [
                [
                    'value' => (string) AlumniAccount::query()->where('is_active', true)->count(),
                    'label' => 'Alumni Terhubung',
                ],
                ['value' => (string) $totalThreads, 'label' => 'Total Topik'],
                ['value' => (string) AlumniForumReply::query()->count(), 'label' => 'Total Balasan'],
                [
                    'value' => (string) AlumniForumThread::query()
                        ->where('created_at', '>=', now()->startOfDay())->count(),
                    'label' => 'Baru Hari Ini',
                ],
            ],
            // "Trending" = paling banyak disukai, bukan daftar terpisah yang
            // harus diurus manual.
            'trending' => AlumniForumThread::query()
                ->with('category')
                ->orderByDesc('like_count')
                ->limit(3)
                ->get()
                ->map(fn (AlumniForumThread $t): array => [
                    'tag' => $t->category->name,
                    'title' => $t->title,
                ]),
            // has(), bukan having(): SQLite menolak HAVING tanpa GROUP BY.
            'top_contributors' => AlumniAccount::query()
                ->withCount('forumThreads')
                ->has('forumThreads')
                ->orderByDesc('alumni_forum_threads_count')
                ->limit(3)
                ->get()
                ->map(fn (AlumniAccount $a): array => [
                    'name' => $a->name,
                    'posts' => $a->forum_threads_count,
                ]),
        ]);
    }
}
