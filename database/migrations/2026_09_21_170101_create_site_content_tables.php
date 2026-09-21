<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Konten situs publik yang dikelola lewat menu My Website.
 *
 * Satu migrasi untuk dua belas tabel karena semuanya satu kesatuan: isinya
 * dikirim bersama lewat satu endpoint (/api/v1/public/site) dan bentuknya
 * sengaja mengikuti lib/content.ts di frontend.
 *
 * Daftar bertingkat (poin program, paragraf berita, langkah layanan) disimpan
 * sebagai JSON, bukan tabel anak: tidak pernah dicari atau disaring satu per
 * satu, selalu dibaca utuh bersama induknya.
 *
 * Kolom `desc` di frontend disimpan sebagai `description` — `desc` adalah kata
 * kunci SQL.
 */
return new class extends Migration
{
    public function up(): void
    {
        // Layanan Cepat — kartu akses layanan digital di beranda.
        Schema::create('digital_services', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->nullable()->unique();
            $table->string('name');
            $table->text('description');
            $table->string('href');
            $table->string('icon');
            $table->string('tone')->default('teal');
            $table->string('login_href')->nullable();
            $table->string('login_label')->nullable();
            $table->string('full_name')->nullable();
            $table->string('audience')->nullable();
            $table->json('about')->nullable();
            $table->json('features')->nullable();
            $table->json('steps')->nullable();
            $table->text('note')->nullable();
            $table->unsignedSmallInteger('sort')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });

        Schema::create('news_posts', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('title');
            $table->string('category');
            $table->date('published_on');
            $table->string('author');
            $table->text('excerpt');
            $table->string('tone')->default('teal');
            $table->json('content');
            $table->string('image')->nullable();
            $table->boolean('is_published')->default(true);
            $table->timestamps();

            $table->index('published_on');
        });

        Schema::create('agenda_items', function (Blueprint $table): void {
            $table->id();
            $table->date('date');
            $table->string('title');
            $table->string('category');
            $table->timestamps();

            $table->index('date');
        });

        Schema::create('programs', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('tag');
            $table->string('icon');
            $table->string('color')->default('teal');
            $table->text('description');
            $table->json('points');
            $table->json('detail');
            $table->json('activities');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('achievements', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->string('student');
            $table->string('level');
            $table->unsignedSmallInteger('year');
            $table->string('organizer');
            $table->string('field');
            $table->timestamps();
        });

        Schema::create('extracurriculars', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('category');
            $table->text('description');
            $table->string('icon');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('facilities', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->text('description');
            $table->string('icon');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('gallery_items', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->date('date');
            $table->string('category');
            $table->string('tone')->default('teal');
            $table->string('image')->nullable();
            $table->timestamps();
        });

        Schema::create('faqs', function (Blueprint $table): void {
            $table->id();
            $table->string('question');
            $table->text('answer');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('testimonials', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('role');
            $table->text('quote');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('alumni', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->unsignedSmallInteger('year');
            $table->string('achievement');
            $table->string('field');
            $table->string('tone')->default('teal');
            $table->text('quote');
            $table->unsignedSmallInteger('sort')->default(0);
            $table->timestamps();
        });

        // Tampilan & Pop-up — pengumuman yang muncul saat beranda dibuka.
        Schema::create('site_popups', function (Blueprint $table): void {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('link_url')->nullable();
            $table->string('link_label')->nullable();
            $table->dateTime('starts_at')->nullable();
            $table->dateTime('ends_at')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        foreach ([
            'site_popups', 'alumni', 'testimonials', 'faqs', 'gallery_items', 'facilities',
            'extracurriculars', 'achievements', 'programs', 'agenda_items', 'news_posts', 'digital_services',
        ] as $tabel) {
            Schema::dropIfExists($tabel);
        }
    }
};
