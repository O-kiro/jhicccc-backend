<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Katalog layanan madrasah.
 *
 * Satu katalog dipakai dua modul: Humas memakainya sebagai jenis kunjungan
 * buku tamu, PTSP sebagai jenis permohonan. Memisahkannya hanya akan membuat
 * dua daftar yang harus dijaga tetap sama.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('target')->nullable();
            $table->text('requirements')->nullable();
            $table->enum('kind', ['layanan', 'kunjungan'])->default('layanan');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};
