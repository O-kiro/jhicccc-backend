<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Balas-ke-balasan dan hapus balasan sendiri.
 *
 * parent_id selalu menunjuk balasan tingkat atas — satu tingkat sarang saja,
 * supaya utas tidak menjorok tanpa batas. Membalas sebuah balasan-anak
 * menempel ke induknya.
 *
 * Penghapusan oleh siswa bersifat lunak (deleted_at): balasan yang punya anak
 * tetap tampil sebagai penanda "telah dihapus", sehingga menghapus tulisan
 * sendiri tidak ikut menghapus tulisan orang lain yang membalasnya.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('forum_replies', function (Blueprint $table): void {
            $table->foreignId('parent_id')->nullable()->after('forum_thread_id')
                ->constrained('forum_replies')->cascadeOnDelete();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('forum_replies', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('parent_id');
            $table->dropSoftDeletes();
        });
    }
};
