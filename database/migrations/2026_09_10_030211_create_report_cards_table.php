<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_cards', function (Blueprint $table) {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('academic_year');                        // "2025/2026"
            $table->string('semester');                             // "Ganjil" | "Genap"
            $table->decimal('average_score', 5, 2);                 // 88.30
            $table->unsignedInteger('class_rank')->nullable();      // 2
            $table->unsignedInteger('class_size')->nullable();      // 32
            $table->decimal('attendance_percentage', 5, 2);         // 98.50
            $table->timestamps();

            $table->unique(['student_id', 'academic_year', 'semester']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_cards');
    }
};
