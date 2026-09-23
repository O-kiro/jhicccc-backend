<?php

namespace App\Http\Controllers\Api\V1\Alumni;

use App\Http\Controllers\Controller;
use App\Models\AlumniAccount;
use App\Models\AlumniForumThread;
use App\Models\AlumniForumThreadLike;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ForumLikeController extends Controller
{
    /**
     * Sakelar suka: satu endpoint, keadaannya dikirim balik supaya klien
     * tidak perlu menebak.
     */
    public function store(Request $request, AlumniForumThread $thread): JsonResponse
    {
        /** @var AlumniAccount $alumni */
        $alumni = $request->user();

        $liked = DB::transaction(function () use ($thread, $alumni): bool {
            $ada = AlumniForumThreadLike::query()
                ->where('alumni_forum_thread_id', $thread->id)
                ->where('alumni_account_id', $alumni->id)
                ->first();

            if ($ada) {
                $ada->delete();
                // Tidak boleh turun di bawah nol walau pencacahnya pernah
                // melenceng dari isi tabel.
                $thread->decrement('like_count', $thread->like_count > 0 ? 1 : 0);

                return false;
            }

            AlumniForumThreadLike::query()->create([
                'alumni_forum_thread_id' => $thread->id,
                'alumni_account_id' => $alumni->id,
            ]);
            $thread->increment('like_count');

            return true;
        });

        return response()->json(['liked' => $liked, 'likes' => $thread->refresh()->like_count]);
    }
}
