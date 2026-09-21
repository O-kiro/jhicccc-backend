<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exam_questions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('exam_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('number');          // urutan tampil, 1..n
            $table->string('type')->default('Pilihan Ganda');
            $table->text('body');
            $table->timestamps();

            $table->unique(['exam_id', 'number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exam_questions');
    }
};
