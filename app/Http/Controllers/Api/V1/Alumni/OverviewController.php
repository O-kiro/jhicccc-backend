<?php

namespace App\Http\Controllers\Api\V1\Alumni;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AlumniResource;
use App\Http\Resources\V1\AnnouncementResource;
use App\Models\AlumniAccount;
use App\Models\AlumniForumThread;
use App\Models\Announcement;
use App\Models\Scholarship;
use App\Support\SebaranAlumni;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OverviewController extends Controller
{
    /**
     * Beranda Portal Alumni. Seluruh angkanya dihitung dari data — rekap
     * sebaran, katalog beasiswa, dan isi forum — bukan angka yang diketik.
     */
    public function __invoke(Request $request): JsonResponse
    {
        /** @var AlumniAccount $alumni */
        $alumni = $request->user();

        $sebaran = SebaranAlumni::tahunTerbaru();
        $beasiswa = Scholarship::query()->aktif();

        return response()->json([
            'alumni' => new AlumniResource($alumni),
            'summary' => [
                'alumni_terdata' => $sebaran['total'],
                'tahun' => $sebaran['year'],
                'lanjut_studi_negeri' => $sebaran['lanjut_studi_negeri'],
                'lanjut_studi_negeri_persen' => $sebaran['lanjut_studi_negeri_persen'],
                'program_beasiswa' => (clone $beasiswa)->count(),
                'kuota_beasiswa' => (int) (clone $beasiswa)->sum('quota'),
                'topik_forum' => AlumniForumThread::query()->count(),
                'anggota_forum' => AlumniAccount::query()->where('is_active', true)->count(),
            ],
            'announcements' => AnnouncementResource::collection(
                Announcement::query()->published()->latest('published_at')->limit(3)->get(),
            ),
        ]);
    }
}
