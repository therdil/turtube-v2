<?php

namespace App\Services;

use App\Models\Video;
use App\Models\VideoAnalytics;

class AnalyticsService
{
    /**
     * Video görüntülenmesini kaydet
     */
    public function recordView(Video $video): void
    {
        // Toplam görüntülenme
        $video->increment('views');

        // Bugünkü analytics kaydını bul
        $analytics = VideoAnalytics::where('video_id', $video->id)
            ->whereDate('date', today())
            ->first();

        // Yoksa oluştur
        if (!$analytics) {

            $analytics = VideoAnalytics::create([
                'video_id'   => $video->id,
                'date'       => today(),
                'views'      => 0,
                'watch_time' => 0,
                'likes'      => 0,
                'comments'   => 0,
            ]);

        }

        // Görüntülenmeyi artır
        $analytics->increment('views');
    }

    /**
     * Günlük beğeni sayısını senkronize et
     */
    public function syncLikes(Video $video): void
    {
        $analytics = VideoAnalytics::firstOrCreate(
            [
                'video_id' => $video->id,
                'date' => today(),
            ]
        );

        $analytics->likes = $video->likes()->count();
        $analytics->save();
    }

    /**
     * Günlük yorum sayısını senkronize et
     */
    public function syncComments(Video $video): void
    {
        $analytics = VideoAnalytics::firstOrCreate(
            [
                'video_id' => $video->id,
                'date' => today(),
            ]
        );

        $analytics->comments = $video->comments()->count();
        $analytics->save();
    }
}