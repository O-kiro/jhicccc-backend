<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Urutan manual untuk galeri dan prestasi.
 *
 * Keduanya disusun berdasarkan kurasi di situs aslinya, bukan tanggal —
 * galeri, misalnya, menaruh foto 2025 di urutan pertama. Mengurutkan ulang
 * berdasarkan tanggal akan mengubah tata letak yang sudah dirancang.
 */
return new class extends Migration
{
    public function up(): void
    {
        foreach (['gallery_items', 'achievements'] as $tabel) {
            Schema::table($tabel, function (Blueprint $table): void {
                $table->unsignedSmallInteger('sort')->default(0);
            });
        }
    }

    public function down(): void
    {
        foreach (['gallery_items', 'achievements'] as $tabel) {
            Schema::table($tabel, function (Blueprint $table): void {
                $table->dropColumn('sort');
            });
        }
    }
};
