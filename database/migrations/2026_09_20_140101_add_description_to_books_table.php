<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Sinopsis buku. Dipakai kartu "Lanjutkan Membaca" di portal siswa, yang
 * sebelumnya memuat teks tetap di kode frontend.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table): void {
            $table->text('description')->nullable()->after('author');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table): void {
            $table->dropColumn('description');
        });
    }
};
