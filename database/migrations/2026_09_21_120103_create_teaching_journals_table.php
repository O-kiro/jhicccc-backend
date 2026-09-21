<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jurnal mengajar per jadwal per tanggal.
 *
 * Keterisiannya yang dipantau modul Jurnal KBM: satu jadwal pada satu tanggal
 * entah sudah terekam atau masih kosong — karena itu pasangan
 * (schedule_id, date) dibuat unik.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teaching_journals', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('schedule_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->string('topic');
            $table->text('note')->nullable();
            $table->unsignedSmallInteger('present_count')->nullable();
            $table->timestamps();

            $table->unique(['schedule_id', 'date']);
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_journals');
    }
};
