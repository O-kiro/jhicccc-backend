<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\ForumThread;
use App\Models\ForumThreadLike;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ForumLikeController extends Controller
{
    /**
     * Menyukai atau membatalkan suka pada sebuah topik.
     *
     * Satu endpoint yang berperilaku sebagai sakelar, bukan sepasang
     * store/destroy: portal hanya punya satu tombol, dan keadaannya sudah
     * dikirim balik di sini sehingga klien tidak perlu menebak.
     */
    public function store(Request $request, ForumThread $thread): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user();

        $liked = DB::transaction(function () use ($thread, $student): bool {
            $existing = ForumThreadLike::query()
                ->where('forum_thread_id', $thread->id)
                ->where('student_id', $student->id)
                ->first();

            if ($existing) {
                $existing->delete();
                // Tidak boleh turun di bawah nol walau pencacahnya pernah
                // melenceng dari isi tabel.
                $thread->decrement('like_count', $thread->like_count > 0 ? 1 : 0);

                return false;
            }

            ForumThreadLike::query()->create([
                'forum_thread_id' => $thread->id,
                'student_id' => $student->id,
            ]);
            $thread->increment('like_count');

            return true;
        });

        return response()->json([
            'liked' => $liked,
            'likes' => $thread->refresh()->like_count,
        ]);
    }
}
