<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ForumReplyResource;
use App\Models\ForumReply;
use App\Models\ForumThread;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\AccessDeniedHttpException;

class ForumReplyController extends Controller
{
    /**
     * Menambahkan balasan pada sebuah topik, atau pada balasan lain.
     */
    public function store(Request $request, ForumThread $thread): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user();

        // Nama kolom untuk pesan galat ada di lang/id/validation.php.
        $data = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:5000'],
            'parent_id' => [
                'nullable',
                'integer',
                // Induk harus balasan di topik yang sama dan belum dihapus.
                Rule::exists('forum_replies', 'id')
                    ->where('forum_thread_id', $thread->id)
                    ->whereNull('deleted_at'),
            ],
        ]);

        $induk = isset($data['parent_id']) ? ForumReply::query()->find($data['parent_id']) : null;

        $reply = ForumReply::query()->create([
            'forum_thread_id' => $thread->id,
            // Satu tingkat sarang: membalas balasan-anak menempel ke induknya.
            'parent_id' => $induk?->parent_id ?? $induk?->id,
            'student_id' => $student->id,
            'body' => $data['body'],
        ]);

        return response()->json(
            new ForumReplyResource($reply->load('student')),
            201,
        );
    }

    /**
     * Menghapus balasan milik sendiri. Bersifat lunak — lihat migrasi
     * add_threading_to_forum_replies.
     */
    public function destroy(Request $request, ForumReply $reply): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user();

        if ($reply->student_id !== $student->id) {
            throw new AccessDeniedHttpException('Hanya penulis yang bisa menghapus balasan ini.');
        }

        $reply->delete();

        return response()->json(['deleted' => true]);
    }
}
