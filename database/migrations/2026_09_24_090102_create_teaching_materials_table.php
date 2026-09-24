<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Koleksi LKPD dan bahan ajar; isinya ditautkan, bukan diunggah. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('teaching_materials', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained()->nullOnDelete();
            // lkpd | bahan_ajar
            $table->string('type')->default('lkpd');
            $table->string('title');
            $table->string('level')->nullable();
            $table->string('url')->nullable();
            $table->text('description')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('teaching_materials');
    }
};
