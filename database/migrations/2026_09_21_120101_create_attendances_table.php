<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kehadiran harian siswa — satu baris per siswa per tanggal.
 *
 * Kode mengikuti buku induk: H hadir, T terlambat, S sakit, I izin,
 * A alpha, L libur.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('attendances', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('date');
            $table->enum('code', ['H', 'T', 'S', 'I', 'A', 'L'])->default('H');
            $table->time('check_in_at')->nullable();
            // Dari mana catatan ini datang: mesin sidik jari, wali kelas,
            // atau wali murid. Dipakai Live Monitoring.
            $table->enum('source', ['fingerprint', 'wali_kelas', 'wali_murid', 'manual'])->default('manual');
            $table->string('note')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('attendances');
    }
};
