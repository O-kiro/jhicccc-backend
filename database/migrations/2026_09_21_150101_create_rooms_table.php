<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Master sarpras: ruang dan fasilitas yang dimiliki madrasah. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('rooms', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('type')->nullable();
            $table->unsignedSmallInteger('capacity')->nullable();
            $table->enum('condition', ['baik', 'rusak_ringan', 'rusak_berat'])->default('baik');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rooms');
    }
};
