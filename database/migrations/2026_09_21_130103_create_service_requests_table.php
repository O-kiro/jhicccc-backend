<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Permohonan yang masuk lewat PTSP beserta perjalanan statusnya. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('service_requests', function (Blueprint $table): void {
            $table->id();
            $table->string('ticket')->unique();
            $table->string('applicant_name');
            $table->string('contact')->nullable();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->text('note')->nullable();
            $table->enum('status', ['baru', 'diproses', 'selesai', 'ditolak'])->default('baru');
            $table->dateTime('submitted_at');
            $table->dateTime('completed_at')->nullable();
            $table->timestamps();

            $table->index('submitted_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('service_requests');
    }
};
