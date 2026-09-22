<?php

namespace App\Http\Controllers\Api\V1\Guru;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\TeacherResource;
use App\Models\Teacher;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfilController extends Controller
{
    /** Identitas guru yang sedang masuk, tanpa pembungkus "data". */
    public function __invoke(Request $request): JsonResponse
    {
        /** @var Teacher $guru */
        $guru = $request->user();

        return response()->json(new TeacherResource($guru->load('homeroomClassrooms')));
    }
}
