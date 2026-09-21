<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/** Agenda surat masuk dan surat keluar. */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('letters', function (Blueprint $table): void {
            $table->id();
            $table->enum('direction', ['masuk', 'keluar']);
            $table->string('number');
            $table->string('subject');
            $table->string('correspondent');
            $table->date('dated_on');
            $table->date('recorded_on');
            $table->string('file_url')->nullable();
            $table->text('note')->nullable();
            $table->timestamps();

            $table->unique(['direction', 'number']);
            $table->index('dated_on');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('letters');
    }
};
