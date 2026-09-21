<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catatan kedisiplinan siswa — satu baris per kejadian.
 *
 * points disalin dari aturan saat pencatatan, bukan dibaca ulang lewat relasi:
 * kalau bobot aturannya diubah tahun depan, klasemen tahun ini tidak ikut
 * berubah sendiri.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discipline_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('discipline_rule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->date('occurred_on');
            $table->smallInteger('points');
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['student_id', 'occurred_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discipline_records');
    }
};
