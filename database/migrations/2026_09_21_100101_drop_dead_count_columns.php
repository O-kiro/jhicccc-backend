<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Membuang courses.module_count dan exams.question_count.
 *
 * Keduanya pencacah yang disimpan terpisah dari isinya. Sejak modul dan soal
 * punya tabel sendiri, API menghitung langsung dari relasi — kolom ini tidak
 * pernah dibaca lagi, hanya ditulis.
 *
 * Dibuang, bukan dibiarkan: begitu muncul di panel admin, guru bisa mengisi
 * "20 modul" pada mata pelajaran yang modulnya cuma tiga, dan angkanya tidak
 * akan pernah cocok dengan apa pun.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->dropColumn('module_count');
        });

        Schema::table('exams', function (Blueprint $table): void {
            $table->dropColumn('question_count');
        });
    }

    public function down(): void
    {
        Schema::table('courses', function (Blueprint $table): void {
            $table->unsignedInteger('module_count')->default(0);
        });

        Schema::table('exams', function (Blueprint $table): void {
            $table->unsignedInteger('question_count')->default(0);
        });
    }
};
