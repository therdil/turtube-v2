<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->boolean('notification_likes_enabled')->default(true);
            $table->boolean('notification_comments_enabled')->default(true);
            $table->boolean('notification_subscribers_enabled')->default(true);
            $table->boolean('notification_system_enabled')->default(true);
            $table->string('channel_visibility', 16)->default('public');
            $table->boolean('subscription_visibility')->default(true);
            $table->string('playlist_visibility', 16)->default('public');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropColumn([
                'notification_likes_enabled', 'notification_comments_enabled',
                'notification_subscribers_enabled', 'notification_system_enabled',
                'channel_visibility', 'subscription_visibility', 'playlist_visibility',
            ]);
        });
    }
};
