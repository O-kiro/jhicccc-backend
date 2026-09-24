<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Berkas RDM (rapor digital madrasah) yang diunggah guru per kelas. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('report_uploads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('teacher_id')->constrained()->cascadeOnDelete();
            $table->foreignId('classroom_id')->constrained()->cascadeOnDelete();
            $table->string('academic_year');
            $table->string('semester');
            $table->string('file_path');
            $table->string('original_name');
            $table->unsignedInteger('size_kb')->default(0);
            $table->text('note')->nullable();
            $table->timestamps();

            $table->index(['teacher_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('report_uploads');
    }
};
