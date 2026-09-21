<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ForumReplyResource;
use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ForumReplyController extends Controller
{
    /**
     * Menambahkan balasan pada sebuah topik.
     */
    public function store(Request $request, ForumThread $thread): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user();

        // Nama kolom untuk pesan galat ada di lang/id/validation.php.
        $data = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:5000'],
        ]);

        $reply = ForumReply::query()->create([
            'forum_thread_id' => $thread->id,
            'student_id' => $student->id,
            'body' => $data['body'],
        ]);

        return response()->json(
            new ForumReplyResource($reply->load('student')),
            201,
        );
    }
}
