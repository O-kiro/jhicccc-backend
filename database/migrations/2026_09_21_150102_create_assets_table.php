<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Buku induk barang: inventaris beserta lokasi dan kondisinya. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('assets', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('category')->nullable();
            $table->foreignId('room_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->date('acquired_on')->nullable();
            $table->unsignedBigInteger('price')->nullable();
            $table->enum('condition', ['baik', 'rusak_ringan', 'rusak_berat'])->default('baik');
            $table->text('note')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('assets');
    }
};
