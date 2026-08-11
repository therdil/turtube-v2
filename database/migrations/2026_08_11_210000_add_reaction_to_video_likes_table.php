<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('video_likes', function (Blueprint $table) {
            $table->string('reaction', 10)->default('like')->after('user_id');
            $table->index(['video_id', 'reaction']);
        });
    }

    public function down(): void
    {
        Schema::table('video_likes', function (Blueprint $table) {
            $table->dropIndex(['video_id', 'reaction']);
            $table->dropColumn('reaction');
        });
    }
};
