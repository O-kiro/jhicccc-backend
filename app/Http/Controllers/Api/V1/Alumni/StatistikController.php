<?php

namespace App\Http\Controllers\Api\V1\Alumni;

use App\Http\Controllers\Controller;
use App\Support\SebaranAlumni;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class StatistikController extends Controller
{
    /** Rekap sebaran kelulusan satu tahun, beserti daftar tahun yang tersedia. */
    public function __invoke(Request $request): JsonResponse
    {
        $tahun = $request->integer('tahun') ?: null;

        return response()->json(SebaranAlumni::untukTahun($tahun));
    }
}
