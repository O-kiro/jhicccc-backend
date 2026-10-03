<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Foto bukti prestasi — sertifikat, medali, atau dokumentasi penyerahan.
 *
 * Kartu prestasi selama ini hanya teks, jadi pengunjung tidak punya cara
 * memverifikasi klaimnya. Fotonya opsional: kartu tanpa foto tetap tampil
 * ringkas seperti sebelumnya, tidak dipaksa memakai petak kosong.
 *
 * Pola dua kolomnya sama dengan berita, fasilitas, dan mitra.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('achievements', function (Blueprint $t): void {
            $t->string('image')->nullable()->after('field');
            $t->string('image_path')->nullable()->after('image');
        });
    }

    public function down(): void
    {
        Schema::table('achievements', function (Blueprint $t): void {
            $t->dropColumn(['image', 'image_path']);
        });
    }
};
