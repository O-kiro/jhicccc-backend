<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Tagihan per siswa per periode.
 *
 * amount disalin dari jenis pembayaran saat tagihan terbit — menaikkan SPP
 * tahun depan tidak boleh mengubah tagihan yang sudah dilunasi.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_type_id')->constrained()->cascadeOnDelete();
            $table->string('period');
            $table->unsignedBigInteger('amount');
            $table->unsignedBigInteger('amount_paid')->default(0);
            $table->date('due_on')->nullable();
            $table->dateTime('paid_at')->nullable();
            $table->string('receipt_no')->nullable();
            $table->timestamps();

            $table->unique(['student_id', 'payment_type_id', 'period']);
            $table->index('due_on');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};
