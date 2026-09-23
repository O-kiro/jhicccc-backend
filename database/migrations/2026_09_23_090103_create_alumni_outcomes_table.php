<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Rekap sebaran kelulusan per tahun.
 *
 * Disimpan sebagai rekap, bukan per orang: madrasah hanya punya angka
 * agregat hasil penelusuran alumni, dan 320 baris data pribadi tidak perlu
 * disimpan hanya untuk menggambar satu diagram.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumni_outcomes', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('year');
            // ptn | pts | kedinasan | kerja
            $table->string('category');
            $table->unsignedInteger('students')->default(0);
            $table->string('note')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->unique(['year', 'category']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni_outcomes');
    }
};
