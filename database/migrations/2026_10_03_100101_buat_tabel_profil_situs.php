<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Profil madrasah dan sambutan kepala madrasah.
 *
 * Dua bagian ini sebelumnya ditanam di lib/content.ts frontend dan tidak
 * pernah melewati CMS, jadi fotonya mustahil diganti lewat panel — satu-satunya
 * cara adalah mengubah kode dan menerbitkan ulang.
 *
 * Tabelnya sengaja menyimpan SATU baris, bukan daftar: ini pengaturan situs,
 * bukan koleksi. Panel menyajikannya sebagai satu formulir, tanpa daftar dan
 * tanpa tombol tambah.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('site_profiles', function (Blueprint $t): void {
            $t->id();

            // Sambutan kepala madrasah
            $t->string('principal_name')->nullable();
            $t->string('principal_role')->nullable();
            $t->text('principal_message')->nullable();
            $t->string('principal_photo')->nullable();
            $t->string('principal_photo_path')->nullable();

            // Foto gedung untuk seksi Tentang MAKOBA
            $t->string('building_photo')->nullable();
            $t->string('building_photo_path')->nullable();

            $t->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('site_profiles');
    }
};
