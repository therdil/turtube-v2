<?php

use App\Models\User;
use App\Models\Video;
use App\Models\VideoAnalytics;
use App\Models\VideoViewEvent;

test('creator analytics displays only the authenticated creators recorded metrics', function () {
    $creator = User::factory()->create();
    $otherCreator = User::factory()->create();

    $creatorsVideo = Video::query()->create([
        'title' => 'Kanal Analytics Videosu',
        'video_path' => 'videos/analytics.mp4',
        'channel_name' => $creator->name,
        'user_id' => $creator->id,
        'duration' => 120,
        'views' => 4,
        'status' => 'public',
    ]);
    $otherVideo = Video::query()->create([
        'title' => 'Başka Kanal Videosu',
        'video_path' => 'videos/other-analytics.mp4',
        'channel_name' => $otherCreator->name,
        'user_id' => $otherCreator->id,
        'duration' => 60,
        'views' => 99,
        'status' => 'public',
    ]);

    VideoAnalytics::query()->create([
        'video_id' => $creatorsVideo->id,
        'date' => today(),
        'views' => 4,
        'watch_time' => 80,
        'impressions' => 16,
    ]);
    VideoAnalytics::query()->create([
        'video_id' => $otherVideo->id,
        'date' => today(),
        'views' => 99,
        'watch_time' => 99,
        'impressions' => 99,
    ]);
    VideoViewEvent::query()->create([
        'video_id' => $creatorsVideo->id,
        'source' => 'Ana Sayfa',
        'device' => 'Mobil',
        'country' => 'TR',
        'viewed_at' => now(),
    ]);

    $this->actingAs($creator)
        ->get(route('studio.analytics.index'))
        ->assertOk()
        ->assertSee('Kanal Analytics Videosu')
        ->assertDontSee('Başka Kanal Videosu')
        ->assertSee('Trafik kaynakları')
        ->assertSee('Ana Sayfa');
});
