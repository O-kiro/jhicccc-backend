<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\ForumReplyResource;
use App\Http\Resources\V1\ForumThreadResource;
use App\Models\ForumThread;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ForumThreadShowController extends Controller
{
    /**
     * Satu topik beserta seluruh balasannya.
     */
    public function __invoke(Request $request, ForumThread $thread): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user();

        $thread->load([
            'category',
            'student',
            // Dibatasi ke siswa ini: yang dibutuhkan hanya "sudah suka atau
            // belum", bukan seluruh daftar penyuka.
            'likes' => fn ($q) => $q->where('student_id', $student->id),
        ])->loadCount('replies');

        // Tingkat atas beserta anak-anaknya. Balasan tingkat atas yang sudah
        // dihapus tetap diambil (withTrashed) hanya bila masih punya anak,
        // supaya balasan orang lain tidak kehilangan konteksnya.
        $replies = $thread->replies()
            ->withTrashed()
            ->whereNull('parent_id')
            ->where(fn ($q) => $q->whereNull('deleted_at')->orWhereHas('children'))
            ->with(['student', 'children' => fn ($q) => $q->with('student')->oldest('created_at')])
            ->oldest('created_at')
            ->get();

        return response()->json([
            'thread' => new ForumThreadResource($thread),
            'replies' => ForumReplyResource::collection($replies),
        ]);
    }
}
