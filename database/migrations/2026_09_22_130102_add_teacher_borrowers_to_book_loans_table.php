<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Guru ikut meminjam buku, lewat portal maupun meja sirkulasi.
 *
 * Peminjam kini salah satu dari student_id atau teacher_id — tepat satu,
 * dijaga di App\Models\BookLoan karena SQLite tidak bisa menambah CHECK ke
 * tabel yang sudah ada tanpa membangunnya ulang.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('book_loans', function (Blueprint $table): void {
            $table->foreignId('student_id')->nullable()->change();
            $table->foreignId('teacher_id')->nullable()->after('student_id')->constrained()->cascadeOnDelete();
            $table->index(['teacher_id', 'returned_at']);
        });
    }

    public function down(): void
    {
        // Pinjaman guru tidak punya tempat di skema lama.
        DB::table('book_loans')->whereNull('student_id')->delete();

        Schema::table('book_loans', function (Blueprint $table): void {
            $table->dropIndex(['teacher_id', 'returned_at']);
            $table->dropConstrainedForeignId('teacher_id');
            $table->foreignId('student_id')->nullable(false)->change();
        });
    }
};
