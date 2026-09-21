<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Kode buku untuk meja sirkulasi.
 *
 * Pembaca barcode dan RFID umumnya berperilaku seperti keyboard: kodenya
 * "diketik" ke kolom yang sedang aktif. Karena itu yang dibutuhkan hanya
 * kode unik yang tertempel di buku, bukan integrasi perangkat keras.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::table('books', function (Blueprint $table): void {
            $table->string('code')->nullable()->unique()->after('id');
        });
    }

    public function down(): void
    {
        Schema::table('books', function (Blueprint $table): void {
            $table->dropUnique(['code']);
            $table->dropColumn('code');
        });
    }
};
