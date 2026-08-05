<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('video_analytics', function (Blueprint $table) {
            $table->unsignedBigInteger('impressions')->default(0)->after('views');
        });
    }

    public function down(): void
    {
        Schema::table('video_analytics', function (Blueprint $table) {
            $table->dropColumn('impressions');
        });
    }
};
