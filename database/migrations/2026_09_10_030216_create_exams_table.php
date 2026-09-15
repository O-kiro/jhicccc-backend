<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('exams', function (Blueprint $table) {
            $table->id();
            $table->foreignId('subject_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->string('title');                    // "Calculus & Integration"
            $table->string('priority')->nullable();     // "Tinggi"
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->unsignedInteger('question_count')->default(0);
            $table->timestamps();

            $table->index(['classroom_id', 'starts_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('exams');
    }
};
