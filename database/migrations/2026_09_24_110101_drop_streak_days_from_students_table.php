<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Daily streak dihapus dari portal: angkanya tidak pernah dipakai untuk
 * apa pun selain hiasan, dan tidak ada yang memperbaruinya.
 *
 * Kolomnya ikut dibuang supaya tidak tertinggal sebagai data mati yang
 * membingungkan pembaca skema berikutnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            $table->dropColumn('streak_days');
        });
    }

    public function down(): void
    {
        Schema::table('students', function (Blueprint $table): void {
            // Nilainya tidak bisa dipulihkan; semua kembali dari nol.
            $table->unsignedInteger('streak_days')->default(0)->after('classroom_id');
        });
    }
};
