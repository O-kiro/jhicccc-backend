<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Models\Achievement;
use App\Models\AgendaItem;
use App\Models\Alumni;
use App\Models\DigitalService;
use App\Models\Extracurricular;
use App\Models\Facility;
use App\Models\Faq;
use App\Models\GalleryItem;
use App\Models\NewsPost;
use App\Models\Program;
use App\Models\SitePopup;
use App\Models\Testimonial;
use Illuminate\Http\JsonResponse;

/**
 * Seluruh konten situs publik dalam satu respons.
 *
 * Bentuk keluarannya sengaja sama persis dengan ekspor lib/content.ts di
 * frontend — nama kunci camelCase, `desc` alih-alih `description`, kunci
 * opsional dihilangkan bila kosong — sehingga komponen situs tidak perlu tahu
 * apakah datanya datang dari API atau dari cadangan bawaan.
 *
 * Tanpa autentikasi: ini isi situs yang memang terbuka untuk umum.
 */
class PublicSiteController extends Controller
{
    public function __invoke(): JsonResponse
    {
        return response()->json([
            'digitalServices' => DigitalService::query()->active()->ordered()->get()->map($this->layanan(...)),
            'news' => NewsPost::query()->published()->orderByDesc('published_on')->orderBy('id')->get()->map($this->berita(...)),
            'agenda' => AgendaItem::query()->orderBy('date')->get()->map(fn (AgendaItem $a): array => [
                'date' => $a->date->toDateString(),
                'title' => $a->title,
                'category' => $a->category,
            ]),
            'programs' => Program::query()->ordered()->get()->map(fn (Program $p): array => [
                'slug' => $p->slug,
                'name' => $p->name,
                'tag' => $p->tag,
                'icon' => $p->icon,
                'color' => $p->color,
                'desc' => $p->description,
                'points' => $p->points,
                'detail' => $p->detail,
                'activities' => $p->activities,
            ]),
            'achievements' => Achievement::query()->ordered()->get()->map(fn (Achievement $a): array => [
                'title' => $a->title,
                'student' => $a->student,
                'level' => $a->level,
                'year' => $a->year,
                'organizer' => $a->organizer,
                'field' => $a->field,
            ]),
            'extracurriculars' => Extracurricular::query()->ordered()->get()->map(fn (Extracurricular $e): array => [
                'name' => $e->name,
                'category' => $e->category,
                'desc' => $e->description,
                'icon' => $e->icon,
            ]),
            'facilities' => Facility::query()->ordered()->get()->map(fn (Facility $f): array => [
                'name' => $f->name,
                'desc' => $f->description,
                'icon' => $f->icon,
            ]),
            'galleryItems' => GalleryItem::query()->ordered()->get()->map(
                fn (GalleryItem $g): array => array_filter([
                    'title' => $g->title,
                    'date' => $g->date->toDateString(),
                    'category' => $g->category,
                    'tone' => $g->tone,
                    'image' => $g->publicImage(),
                ], fn ($v) => $v !== null),
            ),
            'faqs' => Faq::query()->ordered()->get()->map(fn (Faq $f): array => ['q' => $f->question, 'a' => $f->answer]),
            'testimonials' => Testimonial::query()->ordered()->get()->map(fn (Testimonial $t): array => [
                'name' => $t->name,
                'role' => $t->role,
                'quote' => $t->quote,
            ]),
            'alumni' => Alumni::query()->ordered()->get()->map(fn (Alumni $a): array => [
                'name' => $a->name,
                'year' => $a->year,
                'achievement' => $a->achievement,
                'field' => $a->field,
                'tone' => $a->tone,
                'quote' => $a->quote,
            ]),
            'popup' => $this->popup(),
        ]);
    }

    /** @return array<string, mixed> */
    private function layanan(DigitalService $s): array
    {
        $keluaran = [
            'name' => $s->name,
            'desc' => $s->description,
            'href' => $s->href,
            'icon' => $s->icon,
            'tone' => $s->tone,
        ];

        if ($s->login_href) {
            $keluaran['login'] = ['href' => $s->login_href, 'label' => $s->login_label ?? 'Masuk'];
        }

        // Layanan tanpa slug (PPDB) punya halamannya sendiri, jadi tanpa detail.
        if ($s->slug) {
            $keluaran['detail'] = array_filter([
                'slug' => $s->slug,
                'fullName' => $s->full_name ?? $s->name,
                'audience' => $s->audience ?? '',
                'about' => $s->about ?? [],
                'features' => $s->features ?? [],
                'steps' => $s->steps ?? [],
                'note' => $s->note,
            ], fn ($v) => $v !== null);
        }

        return $keluaran;
    }

    /** @return array<string, mixed> */
    private function berita(NewsPost $n): array
    {
        return array_filter([
            'slug' => $n->slug,
            'title' => $n->title,
            'category' => $n->category,
            'date' => $n->published_on->toDateString(),
            'author' => $n->author,
            'excerpt' => $n->excerpt,
            'tone' => $n->tone,
            'content' => $n->content,
            'image' => $n->publicImage(),
        ], fn ($v) => $v !== null);
    }

    /** @return array<string, string|null>|null */
    private function popup(): ?array
    {
        $p = SitePopup::query()->current()->latest('id')->first();

        return $p ? [
            'id' => $p->id,
            'title' => $p->title,
            'body' => $p->body,
            'linkUrl' => $p->link_url,
            'linkLabel' => $p->link_label,
        ] : null;
    }
}
