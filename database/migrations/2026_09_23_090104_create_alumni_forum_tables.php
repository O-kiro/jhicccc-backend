<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Forum Alumni — tabel sendiri, terpisah penuh dari forum siswa.
 *
 * Bentuknya sengaja dibuat sama dengan forum siswa (kategori, topik, balasan
 * bersarang satu tingkat, suka) supaya perilakunya seragam bagi pengguna.
 */
return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alumni_forum_categories', function (Blueprint $table): void {
            $table->id();
            $table->string('name');
            $table->string('description');
            $table->string('icon')->default('chat');
            $table->string('tone')->default('teal');
            $table->unsignedInteger('sort')->default(0);
            $table->timestamps();
        });

        Schema::create('alumni_forum_threads', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('alumni_forum_category_id')->constrained()->cascadeOnDelete();
            $table->foreignId('alumni_account_id')->constrained()->cascadeOnDelete();
            $table->string('title');
            $table->text('body');
            $table->unsignedInteger('like_count')->default(0);
            $table->timestamps();

            $table->index(['alumni_forum_category_id', 'created_at']);
        });

        Schema::create('alumni_forum_replies', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('alumni_forum_thread_id')->constrained()->cascadeOnDelete();
            $table->foreignId('alumni_account_id')->constrained()->cascadeOnDelete();
            // Satu tingkat sarang: balasan atas balasan selalu ditarik ke
            // induk teratas oleh controller.
            $table->foreignId('parent_id')->nullable()->constrained('alumni_forum_replies')->cascadeOnDelete();
            $table->text('body');
            $table->softDeletes();
            $table->timestamps();
        });

        Schema::create('alumni_forum_thread_likes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('alumni_forum_thread_id')->constrained()->cascadeOnDelete();
            $table->foreignId('alumni_account_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['alumni_forum_thread_id', 'alumni_account_id'], 'alumni_like_unik');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alumni_forum_thread_likes');
        Schema::dropIfExists('alumni_forum_replies');
        Schema::dropIfExists('alumni_forum_threads');
        Schema::dropIfExists('alumni_forum_categories');
    }
};
