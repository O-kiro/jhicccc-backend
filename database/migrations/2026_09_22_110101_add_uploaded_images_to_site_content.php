<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Gambar unggahan untuk berita dan galeri.
 *
 * Kolom terpisah dari `image`, bukan menggantikannya: nilai lama berupa jalur
 * di folder public frontend (/photos/...), bukan berkas di penyimpanan
 * Laravel. Kalau kolom itu langsung dipakai komponen unggah, Filament akan
 * menganggap berkasnya hilang dan jalurnya bisa terhapus saat disimpan.
 *
 * Bila keduanya terisi, unggahan yang dipakai.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['news_posts', 'gallery_items'] as $tabel) {
            Schema::table($tabel, function (Blueprint $table): void {
                $table->string('image_path')->nullable()->after('image');
            });
        }
    }

    public function down(): void
    {
        foreach (['news_posts', 'gallery_items'] as $tabel) {
            Schema::table($tabel, function (Blueprint $table): void {
                $table->dropColumn('image_path');
            });
        }
    }
};
