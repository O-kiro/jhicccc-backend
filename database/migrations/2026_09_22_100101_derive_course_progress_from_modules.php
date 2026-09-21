<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Progres kursus dihitung dari modul yang ditandai selesai, bukan diketik.
 *
 * enrollments.progress_percentage adalah angka yang diisi manual dan tidak
 * terhubung ke modul apa pun — bisa menulis 90% pada kursus yang modulnya
 * belum dibuka sama sekali. Kolom itu dibuang.
 *
 * Supaya progres yang sudah ada tidak hilang, persentase lama dikonversi dulu:
 * p% dari n modul → round(p × n / 100) modul pertama ditandai selesai.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('module_completions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('course_module_id')->constrained()->cascadeOnDelete();
            $table->timestamp('completed_at');
            $table->timestamps();

            $table->unique(['student_id', 'course_module_id']);
        });

        $sekarang = now();

        foreach (DB::table('enrollments')->where('progress_percentage', '>', 0)->get() as $e) {
            $modul = DB::table('course_modules')
                ->where('course_id', $e->course_id)
                ->orderBy('number')
                ->pluck('id');

            $jumlah = (int) round($e->progress_percentage * $modul->count() / 100);

            foreach ($modul->take($jumlah) as $idModul) {
                DB::table('module_completions')->insertOrIgnore([
                    'student_id' => $e->student_id,
                    'course_module_id' => $idModul,
                    'completed_at' => $sekarang,
                    'created_at' => $sekarang,
                    'updated_at' => $sekarang,
                ]);
            }
        }

        Schema::table('enrollments', function (Blueprint $table): void {
            $table->dropColumn('progress_percentage');
        });
    }

    public function down(): void
    {
        Schema::table('enrollments', function (Blueprint $table): void {
            $table->unsignedTinyInteger('progress_percentage')->default(0);
        });

        Schema::dropIfExists('module_completions');
    }
};
