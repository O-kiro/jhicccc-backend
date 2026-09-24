<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Jurnal harian guru & tendik: kegiatan di luar jam mengajar kelas.
 *
 * Terpisah dari teaching_journals yang terikat satu jadwal pelajaran.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teacher_activities', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->nullable()->constrained()->nullOnDelete();
            $table->date('date');
            $table->text('activity');
            // Bukti foto, disimpan di disk "public".
            $table->string('photo_path')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teacher_activities');
    }
};
