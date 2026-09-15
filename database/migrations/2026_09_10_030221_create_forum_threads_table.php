<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('forum_threads', function (Blueprint $table) {
            $table->id();
            $table->foreignId('forum_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('student_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->unsignedInteger('like_count')->default(0);
            $table->timestamps();

            $table->index(['forum_category_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('forum_threads');
    }
};
