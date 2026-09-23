<?php

namespace App\Http\Controllers\Api\V1\Alumni;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Alumni\BalasanResource;
use App\Models\AlumniAccount;
use App\Models\AlumniForumReply;
use App\Models\AlumniForumThread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class ForumReplyController extends Controller
{
    public function store(Request $request, AlumniForumThread $thread): JsonResponse
    {
        /** @var AlumniAccount $alumni */
        $alumni = $request->user();

        $data = $request->validate([
            'body' => ['required', 'string', 'min:2', 'max:5000'],
            'parent_id' => [
                'nullable',
                'integer',
                // Induk harus balasan di topik yang sama dan belum dihapus.
                Rule::exists('alumni_forum_replies', 'id')
                    ->where('alumni_forum_thread_id', $thread->id)
                    ->whereNull('deleted_at'),
            ],
        ]);

        $induk = isset($data['parent_id']) ? AlumniForumReply::query()->find($data['parent_id']) : null;

        $reply = AlumniForumReply::query()->create([
            'alumni_forum_thread_id' => $thread->id,
            // Satu tingkat sarang: membalas balasan-anak menempel ke induknya.
            'parent_id' => $induk?->parent_id ?? $induk?->id,
            'alumni_account_id' => $alumni->id,
            'body' => $data['body'],
        ]);

        return response()->json(new BalasanResource($reply->load('author')), 201);
    }

    /** Menghapus balasan sendiri. */
    public function destroy(Request $request, AlumniForumReply $reply): JsonResponse
    {
        /** @var AlumniAccount $alumni */
        $alumni = $request->user();

        // 404, bukan 403: balasan orang lain tidak perlu terlihat ada.
        if ($reply->alumni_account_id !== $alumni->id) {
            throw new NotFoundHttpException('Balasan tidak ditemukan.');
        }

        $reply->delete();

        return response()->json(['deleted' => true]);
    }
}
