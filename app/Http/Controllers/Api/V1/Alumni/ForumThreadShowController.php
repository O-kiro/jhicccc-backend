<?php

namespace App\Http\Controllers\Api\V1\Alumni;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\Alumni\BalasanResource;
use App\Http\Resources\V1\Alumni\TopikResource;
use App\Models\AlumniAccount;
use App\Models\AlumniForumThread;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ForumThreadShowController extends Controller
{
    /** Satu topik beserta seluruh balasannya. */
    public function __invoke(Request $request, AlumniForumThread $thread): JsonResponse
    {
        /** @var AlumniAccount $alumni */
        $alumni = $request->user();

        $thread->load([
            'category',
            'author',
            'likes' => fn ($q) => $q->where('alumni_account_id', $alumni->id),
        ])->loadCount('replies');

        // Balasan tingkat atas yang sudah dihapus tetap diambil bila masih
        // punya anak, supaya balasan orang lain tidak kehilangan konteksnya.
        $replies = $thread->replies()
            ->withTrashed()
            ->whereNull('parent_id')
            ->where(fn ($q) => $q->whereNull('deleted_at')->orWhereHas('children'))
            ->with(['author', 'children' => fn ($q) => $q->with('author')->oldest('created_at')])
            ->oldest('created_at')
            ->get();

        return response()->json([
            'thread' => new TopikResource($thread),
            'replies' => BalasanResource::collection($replies),
        ]);
    }
}
