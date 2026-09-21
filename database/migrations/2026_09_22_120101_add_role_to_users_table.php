<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Peran pengguna panel admin. Lihat App\Support\Peran untuk daftar peran dan
 * menu yang boleh dibuka masing-masing.
 *
 * Akun yang sudah ada dijadikan super_admin supaya tidak ada yang terkunci
 * di luar panel setelah pembaruan ini. Akun baru wajib diberi peran saat
 * dibuat; akun tanpa peran tidak bisa masuk panel sama sekali.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('role')->nullable()->after('email');
        });

        DB::table('users')->whereNull('role')->update(['role' => 'super_admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn('role');
        });
    }
};
