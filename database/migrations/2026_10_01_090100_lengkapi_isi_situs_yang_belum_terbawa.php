<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Menambah isi yang sudah dipakai frontend tapi belum dibawa basis data.
 *
 * Ketiganya bertipe opsional di lib/content.ts, jadi situs tidak rusak tanpa
 * mereka — hanya memakai teks atau petak warna bawaan. Tapi selama kolomnya
 * belum ada, isi rancangan itu mustahil diatur lewat panel admin: begitu API
 * terjangkau, isinya menggantikan cadangan di lib/content.ts dan bagian yang
 * tidak dibawa API ikut hilang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('digital_services', function (Blueprint $t): void {
            // Deskripsi di kartu judul halaman detail; tanpa ini dipakai `description`.
            $t->text('intro')->nullable()->after('audience');
            // Catatan kecil di bawah tombol masuk.
            $t->text('login_note')->nullable()->after('login_label');
            // Tombol kedua di kartu judul: {label, href}.
            $t->json('guide')->nullable()->after('login_note');
            // Kartu fitur di kolom utama: [{title, desc}].
            $t->json('highlights')->nullable()->after('about');
            // Judul kartu fitur di sidebar; bawaannya "Fitur Utama".
            $t->string('features_title')->nullable()->after('highlights');
            // Kartu bantuan di sidebar: {title, text, label}.
            $t->json('help')->nullable()->after('note');
        });

        // Pola dua kolomnya meniru news_posts dan gallery_items: `image` untuk
        // berkas statis di /public, `image_path` untuk unggahan admin.
        Schema::table('facilities', function (Blueprint $t): void {
            $t->string('image')->nullable()->after('icon');
            $t->string('image_path')->nullable()->after('image');
        });

        Schema::table('testimonials', function (Blueprint $t): void {
            $t->string('photo')->nullable()->after('quote');
            $t->string('photo_path')->nullable()->after('photo');
        });
    }

    public function down(): void
    {
        Schema::table('digital_services', function (Blueprint $t): void {
            $t->dropColumn(['intro', 'login_note', 'guide', 'highlights', 'features_title', 'help']);
        });

        Schema::table('facilities', function (Blueprint $t): void {
            $t->dropColumn(['image', 'image_path']);
        });

        Schema::table('testimonials', function (Blueprint $t): void {
            $t->dropColumn(['photo', 'photo_path']);
        });
    }
};
