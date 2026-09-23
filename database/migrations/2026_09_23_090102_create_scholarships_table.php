<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Katalog Portal Beasiswa.
 *
 * Angka ringkasan di portal (program aktif, kuota, penyerapan) dihitung dari
 * baris-baris ini, bukan disimpan terpisah — supaya tidak pernah berselisih
 * dengan katalognya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('scholarships', function (Blueprint $table): void {
            $table->id();
            $table->string('category');
            $table->string('name');
            $table->unsignedSmallInteger('quota')->default(0);
            $table->text('benefits')->nullable();
            $table->string('target')->nullable();
            $table->date('deadline')->nullable();
            // dibuka | segera_ditutup | ditutup
            $table->string('status')->default('dibuka');
            $table->unsignedInteger('applicants')->default(0);
            $table->unsignedInteger('verified')->default(0);
            $table->string('url')->nullable();
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();

            $table->index(['status', 'sort']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('scholarships');
    }
};
