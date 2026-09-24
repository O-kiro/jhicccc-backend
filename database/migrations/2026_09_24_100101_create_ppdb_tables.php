<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Pendaftar PPDB dan berkas unggahannya.
 *
 * Akunnya dibuat panitia, bukan swa-daftar: nomor pendaftaran diterbitkan
 * saat calon siswa mendaftar di loket atau PPDB Online, lalu dipakai untuk
 * mengunggah berkas. Sandi boleh kosong — berarti belum diberi akses.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('ppdb_registrants', function (Blueprint $table): void {
            $table->id();
            $table->string('registration_number')->unique();
            $table->string('name');
            // Prestasi | Reguler 1 | Reguler 2
            $table->string('jalur');
            $table->string('password')->nullable();
            $table->string('phone')->nullable();
            $table->string('origin_school')->nullable();
            $table->boolean('is_active')->default(true);
            $table->text('note')->nullable();
            $table->timestamps();
        });

        Schema::create('ppdb_documents', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('ppdb_registrant_id')->constrained()->cascadeOnDelete();
            $table->string('jenis');
            $table->string('file_path');
            $table->string('original_name');
            $table->unsignedInteger('size_kb')->default(0);
            // menunggu | diterima | ditolak
            $table->string('status')->default('menunggu');
            $table->text('note')->nullable();
            $table->timestamp('verified_at')->nullable();
            $table->foreignId('verified_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            // Satu jenis berkas satu baris; mengunggah ulang menggantinya.
            $table->unique(['ppdb_registrant_id', 'jenis']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ppdb_documents');
        Schema::dropIfExists('ppdb_registrants');
    }
};
