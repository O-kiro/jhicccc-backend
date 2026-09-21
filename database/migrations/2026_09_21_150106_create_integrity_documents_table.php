<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Dokumen bukti dukung Zona Integritas, dikelompokkan per area perubahan. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integrity_documents', function (Blueprint $table): void {
            $table->id();
            $table->string('area');
            $table->string('title');
            $table->text('description')->nullable();
            $table->string('file_url')->nullable();
            $table->year('year');
            $table->enum('status', ['draf', 'terkumpul', 'terverifikasi'])->default('draf');
            $table->timestamps();

            $table->index(['year', 'area']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integrity_documents');
    }
};
