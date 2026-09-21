<?php

namespace Database\Seeders;

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
use App\Models\Testimonial;
use Illuminate\Database\Seeder;

/**
 * Memindahkan isi situs publik ke basis data.
 *
 * Sumbernya data/situs.json — diekspor langsung dari lib/content.ts di
 * frontend, bukan diketik ulang, supaya situs tampil persis sama setelah
 * beralih ke CMS. Urutan asli dipertahankan lewat kolom sort.
 *
 * Idempoten: dicocokkan lewat slug atau judul, jadi aman dijalankan ulang
 * tanpa menggandakan isi.
 */
class SitusSeeder extends Seeder
{
    public function run(): void
    {
        $data = json_decode((string) file_get_contents(database_path('seeders/data/situs.json')), true);

        foreach ($data['digitalServices'] as $i => $s) {
            $d = $s['detail'] ?? [];
            DigitalService::query()->updateOrCreate(['name' => $s['name']], [
                'slug' => $d['slug'] ?? null,
                'description' => $s['desc'],
                'href' => $s['href'],
                'icon' => $s['icon'],
                'tone' => $s['tone'],
                'login_href' => $s['login']['href'] ?? null,
                'login_label' => $s['login']['label'] ?? null,
                'full_name' => $d['fullName'] ?? null,
                'audience' => $d['audience'] ?? null,
                'about' => $d['about'] ?? null,
                'features' => $d['features'] ?? null,
                'steps' => $d['steps'] ?? null,
                'note' => $d['note'] ?? null,
                'sort' => $i,
            ]);
        }

        foreach ($data['news'] as $n) {
            NewsPost::query()->updateOrCreate(['slug' => $n['slug']], [
                'title' => $n['title'],
                'category' => $n['category'],
                'published_on' => $n['date'],
                'author' => $n['author'],
                'excerpt' => $n['excerpt'],
                'tone' => $n['tone'],
                'content' => $n['content'],
                'image' => $n['image'] ?? null,
            ]);
        }

        foreach ($data['agenda'] as $a) {
            // Dicocokkan lewat judul saja. Kolom date tersimpan sebagai
            // "2026-07-07 00:00:00", jadi mencocokkan dengan "2026-07-07"
            // tidak pernah berhasil — dan tiap seeder jalan, agenda tergandakan.
            AgendaItem::query()->updateOrCreate(['title' => $a['title']], [
                'date' => $a['date'],
                'category' => $a['category'],
            ]);
        }

        foreach ($data['programs'] as $i => $p) {
            Program::query()->updateOrCreate(['slug' => $p['slug']], [
                'name' => $p['name'],
                'tag' => $p['tag'],
                'icon' => $p['icon'],
                'color' => $p['color'],
                'description' => $p['desc'],
                'points' => $p['points'],
                'detail' => $p['detail'],
                'activities' => $p['activities'],
                'sort' => $i,
            ]);
        }

        foreach ($data['achievements'] as $i => $a) {
            Achievement::query()->updateOrCreate(['title' => $a['title'], 'student' => $a['student']], [
                'level' => $a['level'],
                'year' => $a['year'],
                'organizer' => $a['organizer'],
                'field' => $a['field'],
                'sort' => $i,
            ]);
        }

        foreach ($data['extracurriculars'] as $i => $e) {
            Extracurricular::query()->updateOrCreate(['name' => $e['name']], [
                'category' => $e['category'],
                'description' => $e['desc'],
                'icon' => $e['icon'],
                'sort' => $i,
            ]);
        }

        foreach ($data['facilities'] as $i => $f) {
            Facility::query()->updateOrCreate(['name' => $f['name']], [
                'description' => $f['desc'],
                'icon' => $f['icon'],
                'sort' => $i,
            ]);
        }

        foreach ($data['galleryItems'] as $i => $g) {
            GalleryItem::query()->updateOrCreate(['title' => $g['title']], [
                'date' => $g['date'],
                'category' => $g['category'],
                'tone' => $g['tone'],
                'image' => $g['image'] ?? null,
                'sort' => $i,
            ]);
        }

        foreach ($data['faqs'] as $i => $q) {
            Faq::query()->updateOrCreate(['question' => $q['q']], ['answer' => $q['a'], 'sort' => $i]);
        }

        foreach ($data['testimonials'] as $i => $t) {
            Testimonial::query()->updateOrCreate(['name' => $t['name']], [
                'role' => $t['role'],
                'quote' => $t['quote'],
                'sort' => $i,
            ]);
        }

        foreach ($data['alumni'] as $i => $a) {
            Alumni::query()->updateOrCreate(['name' => $a['name']], [
                'year' => $a['year'],
                'achievement' => $a['achievement'],
                'field' => $a['field'],
                'tone' => $a['tone'],
                'quote' => $a['quote'],
                'sort' => $i,
            ]);
        }
    }
}
