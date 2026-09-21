<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Catatan layanan bimbingan dan konseling.
 *
 * is_confidential menandai catatan yang hanya untuk Guru BK. Pembatasannya
 * ditegakkan di CounselingSessionResource::getEloquentQuery().
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('counseling_sessions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('teacher_id')->nullable()->constrained()->nullOnDelete();
            $table->date('held_on');
            $table->string('category');
            $table->text('summary');
            $table->text('follow_up')->nullable();
            $table->boolean('is_confidential')->default(false);
            $table->timestamps();

            $table->index(['student_id', 'held_on']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('counseling_sessions');
    }
};
