<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Akses Portal Guru.
 *
 * Sandi sengaja boleh kosong: tidak semua guru perlu masuk portal, dan guru
 * tanpa sandi otomatis tidak bisa masuk — tidak ada sandi bawaan yang bisa
 * ditebak. Admin mengisinya dari Data Master → Guru.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('teachers', function (Blueprint $table): void {
            $table->string('password')->nullable()->after('email');
            $table->boolean('is_active')->default(true)->after('password');
        });
    }

    public function down(): void
    {
        Schema::table('teachers', function (Blueprint $table): void {
            $table->dropColumn(['password', 'is_active']);
        });
    }
};
