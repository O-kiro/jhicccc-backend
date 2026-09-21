<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Siapa menyukai topik apa.
 *
 * Kolom forum_threads.like_count tetap dipakai sebagai angka yang ditampilkan;
 * tabel ini yang membuat tombol suka bisa dimatikan-hidupkan dan mencegah satu
 * siswa menyukai topik yang sama berkali-kali.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_thread_likes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('forum_thread_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['forum_thread_id', 'student_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_thread_likes');
    }
};
