<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Buku tata tertib: daftar pelanggaran dan penghargaan beserta bobot poinnya.
 *
 * Dipisah dari catatannya supaya bobot poin punya satu sumber — mengubah
 * bobot di sini tidak menulis ulang sejarah, karena tiap catatan menyimpan
 * poin yang berlaku saat kejadian.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('discipline_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('title');
            $table->text('description')->nullable();
            // pelanggaran menambah poin, penghargaan menguranginya.
            $table->enum('kind', ['pelanggaran', 'penghargaan'])->default('pelanggaran');
            $table->string('category')->nullable();
            $table->unsignedSmallInteger('points')->default(0);
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('discipline_rules');
    }
};
