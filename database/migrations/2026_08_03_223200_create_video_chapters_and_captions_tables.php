<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_chapters', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->string('title', 120);
            $table->unsignedInteger('start_seconds');
            $table->timestamps();

            $table->unique(['video_id', 'start_seconds']);
        });

        Schema::create('video_captions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->string('language', 10);
            $table->string('label', 80);
            $table->string('path');
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_captions');
        Schema::dropIfExists('video_chapters');
    }
};
