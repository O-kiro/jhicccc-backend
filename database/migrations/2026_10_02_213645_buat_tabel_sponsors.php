<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Logo mitra dan pendukung yang tampil berjalan di beranda.
 *
 * Isinya dikelola lewat panel seperti isi situs lainnya, bukan ditanam di
 * kode: daftar mitra berubah tiap tahun, dan tiap perubahan tidak perlu
 * menunggu rilis.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sponsors', function (Blueprint $t): void {
            $t->id();
            $t->string('name');
            // Pola dua kolom seperti berita dan fasilitas: `logo` untuk berkas
            // statis di /public, `logo_path` untuk unggahan admin.
            $t->string('logo')->nullable();
            $t->string('logo_path')->nullable();
            // Opsional — tidak semua mitra punya situs.
            $t->string('url')->nullable();
            $t->unsignedInteger('sort')->default(0);
            $t->boolean('is_active')->default(true);
            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sponsors');
    }
};
