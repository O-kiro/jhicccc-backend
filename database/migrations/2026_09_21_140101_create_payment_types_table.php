<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Master pembayaran: jenis tagihan beserta nominal bakunya. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            // Rupiah disimpan sebagai bilangan bulat, bukan desimal: uang
            // tidak pernah dihitung dengan pecahan biner.
            $table->unsignedBigInteger('amount')->default(0);
            $table->enum('period', ['bulanan', 'semester', 'tahunan', 'sekali'])->default('bulanan');
            $table->boolean('is_active')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_types');
    }
};
