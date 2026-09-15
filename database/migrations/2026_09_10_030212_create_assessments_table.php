<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assessments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->string('title')->nullable();
            $table->unsignedTinyInteger('score');       // 0-100
            $table->date('assessed_on');                // dipakai grafik "Sejarah Nilai" per bulan
            $table->timestamps();

            $table->index(['student_id', 'assessed_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assessments');
    }
};
