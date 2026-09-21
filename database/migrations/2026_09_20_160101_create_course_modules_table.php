<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modul pembelajaran per mata pelajaran.
 *
 * Materinya sendiri tidak disimpan di sini, hanya ditautkan (Google Drive,
 * YouTube, dan sejenisnya) — itu cara yang sudah biasa dipakai madrasah dan
 * tidak menuntut penyimpanan berkas di server.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('course_modules', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('course_id')->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('number');
            $table->string('title');
            $table->text('description')->nullable();
            // Boleh kosong: modul bisa didaftarkan lebih dulu, materinya
            // menyusul. Portal menampilkannya sebagai "materi belum tersedia".
            $table->string('url')->nullable();
            $table->timestamps();

            $table->unique(['course_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('course_modules');
    }
};
