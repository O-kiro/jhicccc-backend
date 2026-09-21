<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Buku kas umum dana komite: pemasukan dan pengeluaran. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cash_entries', function (Blueprint $table): void {
            $table->id();
            $table->date('entry_date');
            $table->enum('direction', ['masuk', 'keluar']);
            $table->string('category');
            $table->string('description');
            $table->unsignedBigInteger('amount');
            $table->string('reference')->nullable();
            $table->timestamps();

            $table->index('entry_date');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('cash_entries');
    }
};
