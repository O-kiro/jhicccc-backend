<?php

namespace App\Http\Controllers\Api\V1\Alumni;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AlumniResource;
use App\Models\AlumniAccount;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ProfilController extends Controller
{
    /** Identitas alumni yang sedang masuk, tanpa pembungkus "data". */
    public function __invoke(Request $request): JsonResponse
    {
        /** @var AlumniAccount $alumni */
        $alumni = $request->user();

        return response()->json(new AlumniResource($alumni));
    }
}
