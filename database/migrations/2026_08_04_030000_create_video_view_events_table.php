<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('video_view_events', function (Blueprint $table) {
            $table->id();
            $table->foreignId('video_id')->constrained()->cascadeOnDelete();
            $table->string('source', 32)->default('Doğrudan');
            $table->string('device', 16)->default('Masaüstü');
            $table->string('country', 8)->default('Bilinmiyor');
            $table->timestamp('viewed_at')->useCurrent();
            $table->index(['video_id', 'viewed_at']);
            $table->index(['source', 'viewed_at']);
            $table->index(['device', 'viewed_at']);
            $table->index(['country', 'viewed_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('video_view_events');
    }
};
