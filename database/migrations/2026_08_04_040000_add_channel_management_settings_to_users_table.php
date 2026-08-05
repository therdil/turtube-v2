<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->json('channel_tags')->nullable()->after('social_links');
            $table->json('seo_keywords')->nullable()->after('channel_tags');
            $table->string('channel_language', 10)->default('tr')->after('seo_keywords');
            $table->string('default_video_status', 20)->default('public')->after('channel_language');
            $table->text('default_video_description')->nullable()->after('default_video_status');
            $table->string('default_video_license', 30)->default('standard')->after('default_video_description');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['channel_tags', 'seo_keywords', 'channel_language', 'default_video_status', 'default_video_description', 'default_video_license']);
        });
    }
};
