<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('videos', function (Blueprint $table) {
            $table->index(['status', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['category_id', 'status', 'created_at']);
        });

        Schema::table('video_analytics', function (Blueprint $table) {
            $table->index('date');
        });
    }

    public function down(): void
    {
        Schema::table('video_analytics', function (Blueprint $table) {
            $table->dropIndex(['date']);
        });

        Schema::table('videos', function (Blueprint $table) {
            $table->dropIndex(['status', 'created_at']);
            $table->dropIndex(['user_id', 'created_at']);
            $table->dropIndex(['category_id', 'status', 'created_at']);
        });
    }
};
