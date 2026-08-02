<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_analytics', function (Blueprint $table) {

            $table->id();

            $table->foreignId('video_id')
                ->constrained()
                ->cascadeOnDelete();

            $table->date('date');

            $table->unsignedInteger('views')->default(0);

            $table->unsignedInteger('watch_time')->default(0);

            $table->unsignedInteger('likes')->default(0);

            $table->unsignedInteger('comments')->default(0);

            $table->timestamps();

            $table->unique([
                'video_id',
                'date'
            ]);

        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_analytics');
    }
};