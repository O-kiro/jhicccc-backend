<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Excel RDM yang ditandai guru tampil sebagai tabel di halaman
        // Ranking portal siswa kelas itu.
        Schema::table('report_uploads', function (Blueprint $table) {
            $table->boolean('shown_to_students')->default(false);
        });
    }

    public function down(): void
    {
        Schema::table('report_uploads', function (Blueprint $table) {
            $table->dropColumn('shown_to_students');
        });
    }
};
