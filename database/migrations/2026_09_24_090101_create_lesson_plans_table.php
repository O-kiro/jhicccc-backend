<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Modul ajar (TP/ATP) milik guru.
 *
 * Berbeda dari course_modules: yang itu materi kursus yang dilihat siswa di
 * portalnya, ini perangkat pembelajaran milik guru — dipakai sesama guru,
 * tidak tampil di portal siswa.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('lesson_plans', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained()->nullOnDelete();
            $table->string('title');
            // Jam pelajaran seperti tertulis di jadwal, mis. "07.45–09.15".
            $table->string('time_range')->nullable();
            $table->string('url')->nullable();
            $table->text('note')->nullable();
            // aktif | arsip
            $table->string('status')->default('aktif');
            $table->timestamps();

            $table->index(['teacher_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('lesson_plans');
    }
};
