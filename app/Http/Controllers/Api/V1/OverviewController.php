<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AnnouncementResource;
use App\Http\Resources\V1\ScheduleResource;
use App\Http\Resources\V1\StudentResource;
use App\Models\Announcement;
use App\Models\Schedule;
use App\Models\Student;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class OverviewController extends Controller
{
    /**
     * Data halaman Overview portal siswa: ringkasan, jadwal hari ini,
     * dan pengumuman terbaru dalam satu permintaan.
     */
    public function __invoke(Request $request): JsonResponse
    {
        /** @var Student $student */
        $student = $request->user();
        $student->load('classroom');

        $schedules = Schedule::query()
            ->where('classroom_id', $student->classroom_id)
            ->forToday()
            ->with(['subject', 'teacher'])
            ->orderBy('starts_at')
            ->get();

        $announcements = Announcement::query()
            ->published()
            ->latest('published_at')
            ->limit(3)
            ->get();

        $reportCard = $student->reportCards()->latest('id')->first();

        return response()->json([
            'student' => new StudentResource($student),
            'summary' => [
                'average_score' => $reportCard ? (float) $reportCard->average_score : null,
                'attendance_percentage' => $reportCard ? (float) $reportCard->attendance_percentage : null,
                'streak_days' => $student->streak_days,
            ],
            'today_schedule' => ScheduleResource::collection($schedules),
            'announcements' => AnnouncementResource::collection($announcements),
        ]);
    }
}
