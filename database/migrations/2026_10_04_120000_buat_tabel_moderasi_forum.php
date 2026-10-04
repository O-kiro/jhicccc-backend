<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_moderations', function (Blueprint $table) {
            $table->id();
            $table->string('forum', 10);            // siswa | alumni
            $table->string('jenis', 10);            // topik | balasan
            $table->string('status', 15)->index();  // ditolak | belum_dicek | dipulihkan | aman | dihapus
            // Penulis: Student atau AlumniAccount.
            $table->nullableMorphs('penulis');
            // Data persis yang dikirim penulis, cukup untuk membuat ulang
            // postingannya bila admin memulihkan.
            $table->json('payload');
            $table->text('alasan')->nullable();
            // Postingan yang tayang (belum_dicek / dipulihkan).
            $table->nullableMorphs('postingan');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_moderations');
    }
};
