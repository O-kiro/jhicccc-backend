<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_question_options', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_question_id')->constrained()->cascadeOnDelete();
            $table->string('key', 2);                   // A, B, C, D, E
            $table->text('body');
            // Kunci jawaban. Tidak pernah dikirim ke portal siswa — lihat
            // ExamQuestionResource, yang sengaja tidak memuat kolom ini.
            $table->boolean('is_correct')->default(false);
            $table->timestamps();

            $table->unique(['exam_question_id', 'key']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_question_options');
    }
};
