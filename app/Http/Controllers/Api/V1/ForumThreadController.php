<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ForumThreadResource;
use App\Models\ForumThread;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ForumThreadController extends Controller
{
    /**
     * Membuka topik diskusi baru atas nama siswa yang sedang masuk.
     */
    public function store(Request $request): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user();

        // Nama kolom untuk pesan galat ada di lang/id/validation.php.
        $data = $request->validate([
            'forum_category_id' => ['required', Rule::exists('forum_categories', 'id')],
            'title' => ['required', 'string', 'min:10', 'max:150'],
            'body' => ['required', 'string', 'min:20', 'max:5000'],
        ]);

        $thread = ForumThread::query()->create([
            ...$data,
            'student_id' => $student->id,
            // Penulisnya sendiri tidak memberi suka di awal.
            'like_count' => 0,
        ]);

        $thread->load(['category', 'student'])->loadCount('replies');

        return response()->json(new ForumThreadResource($thread), 201);
    }
}
