<?php

namespace App\Http\Controllers\Api\V1\Alumni;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Alumni\TopikResource;
use App\Models\AlumniAccount;
use App\Models\AlumniForumThread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ForumThreadController extends Controller
{
    /** Membuka topik baru atas nama alumni yang sedang masuk. */
    public function store(Request $request): JsonResponse
    {
        /** @var AlumniAccount $alumni */
        $alumni = $request->user();

        // Nama kolom untuk pesan galat ada di lang/id/validation.php.
        $data = $request->validate([
            'alumni_forum_category_id' => ['required', Rule::exists('alumni_forum_categories', 'id')],
            'title' => ['required', 'string', 'min:10', 'max:150'],
            'body' => ['required', 'string', 'min:20', 'max:5000'],
        ]);

        $thread = AlumniForumThread::query()->create([
            ...$data,
            'alumni_account_id' => $alumni->id,
            // Penulisnya sendiri tidak memberi suka di awal.
            'like_count' => 0,
        ]);

        $thread->load(['category', 'author'])->loadCount('replies');

        return response()->json(new TopikResource($thread), 201);
    }
}
