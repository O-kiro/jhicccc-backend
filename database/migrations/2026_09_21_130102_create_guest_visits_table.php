<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Buku tamu: log kunjungan beserta penilaian layanannya. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('guest_visits', function (Blueprint $table): void {
            $table->id();
            $table->string('registration_code')->unique();
            $table->string('guest_name');
            $table->string('institution')->nullable();
            $table->string('phone')->nullable();
            $table->foreignId('service_id')->nullable()->constrained()->nullOnDelete();
            $table->text('purpose');
            $table->dateTime('arrived_at');
            $table->dateTime('finished_at')->nullable();
            $table->enum('status', ['pending', 'kunjungan', 'selesai'])->default('pending');
            // 1–5; kosong berarti tamunya belum memberi penilaian.
            $table->unsignedTinyInteger('rating')->nullable();
            $table->timestamps();

            $table->index('arrived_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('guest_visits');
    }
};
