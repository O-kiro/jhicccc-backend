<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\V1\AnnouncementResource;
use App\Models\Announcement;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class AnnouncementController extends Controller
{
    /**
     * Daftar pengumuman madrasah, terbaru lebih dulu.
     */
    public function index(): AnonymousResourceCollection
    {
        return AnnouncementResource::collection(
            Announcement::query()
                ->published()
                ->latest('published_at')
                ->paginate(10),
        );
    }
}
