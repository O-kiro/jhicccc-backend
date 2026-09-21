<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Peminjaman dan booking ruang maupun barang.
 *
 * Keduanya di satu tabel karena alurnya sama persis — yang membedakan hanya
 * apa yang dipinjam, dan tepat satu di antara room_id atau asset_id terisi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asset_bookings', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('room_id')->nullable()->constrained()->cascadeOnDelete();
            $table->foreignId('asset_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('borrower');
            $table->text('purpose');
            $table->dateTime('starts_at');
            $table->dateTime('ends_at');
            $table->dateTime('returned_at')->nullable();
            $table->timestamps();

            $table->index('starts_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_bookings');
    }
};
