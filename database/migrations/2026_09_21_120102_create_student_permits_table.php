<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Log izin keluar-masuk yang dicatat guru piket.
 *
 * returned_at kosong berarti siswanya belum kembali — itu yang dipantau
 * guru piket sepanjang hari.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('student_permits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->enum('kind', ['keluar_kelas', 'masuk_kelas', 'keluar_sekolah'])->default('keluar_kelas');
            $table->dateTime('left_at');
            $table->dateTime('returned_at')->nullable();
            $table->string('reason');
            $table->timestamps();

            $table->index('left_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('student_permits');
    }
};
