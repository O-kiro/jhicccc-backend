<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('book_loans', function (Blueprint $table) {
            $table->id();
            $table->foreignId('book_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->date('due_on');
            $table->timestamp('returned_at')->nullable();
            $table->unsignedInteger('current_page')->default(0);
            $table->timestamps();

            $table->index(['student_id', 'returned_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('book_loans');
    }
};
