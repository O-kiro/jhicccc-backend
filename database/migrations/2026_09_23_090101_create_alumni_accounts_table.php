<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Akun Portal Alumni.
 *
 * Terpisah dari tabel `alumni` — yang itu etalase profil alumni di situs
 * publik (dikelola CMS), bukan orang yang bisa masuk. Seperti guru, sandi
 * boleh kosong: berarti admin belum memberi akses.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumni_accounts', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('email')->unique();
            $table->string('password')->nullable();
            $table->unsignedSmallInteger('graduation_year');
            $table->string('occupation')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni_accounts');
    }
};
