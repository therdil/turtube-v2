<?php

namespace App\Services;

use App\Models\Video;
use App\Models\VideoAnalytics;
use App\Models\VideoViewEvent;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;

class AnalyticsService
{
    private ?bool $impressionsSupported = null;

    /**
     * Video görüntülenmesini kaydet
     */
    public function recordView(Video $video, ?Request $request = null): void
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

        if ($request) {
            VideoViewEvent::create([
                'video_id' => $video->id,
                'source' => $this->trafficSource($request),
                'device' => $this->deviceType($request->userAgent() ?? ''),
                'country' => $this->countryCode($request),
                'viewed_at' => now(),
            ]);
        }
    }

    /**
     * Oynatıcıdan gelen gerçek izleme ilerlemesini günlük toplama ekler.
     */
    public function recordWatchTime(Video $video, int $seconds): void
    {
        if ($seconds <= 0) {
            return;
        }

        $analytics = VideoAnalytics::firstOrCreate(
            [
                'video_id' => $video->id,
                'date' => today(),
            ],
            [
                'views' => 0,
                'watch_time' => 0,
                'likes' => 0,
                'comments' => 0,
            ]
        );

        $analytics->increment('watch_time', $seconds);
    }

    /**
     * Ana sayfada gösterilen kartları günlük gösterim toplamına ekler.
     * Aynı sayfada tekrarlanan video varsa tek gösterim olarak sayılır.
     *
     * @param Collection<int, Video> $videos
     */
    public function recordImpressions(Collection $videos): void
    {
        if (! ($this->impressionsSupported ??= Schema::hasColumn('video_analytics', 'impressions'))) {
            return;
        }

        $videos->unique('id')->each(function (Video $video): void {
            $analytics = VideoAnalytics::firstOrCreate(
                ['video_id' => $video->id, 'date' => today()],
                ['views' => 0, 'watch_time' => 0, 'likes' => 0, 'comments' => 0, 'impressions' => 0]
            );

            $analytics->increment('impressions');
        });
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

    private function trafficSource(Request $request): string
    {
        $referer = (string) $request->headers->get('referer', '');

        if ($referer === '') {
            return 'Doğrudan';
        }

        $refererHost = parse_url($referer, PHP_URL_HOST);

        if ($refererHost && strcasecmp($refererHost, (string) $request->getHost()) !== 0) {
            return 'Dış bağlantılar';
        }

        $path = (string) parse_url($referer, PHP_URL_PATH);

        return match (true) {
            $path === '/' || $path === '' => 'Ana Sayfa',
            str_starts_with($path, '/search') => 'Arama',
            str_starts_with($path, '/@') => 'Kanal',
            default => 'TurTube içi',
        };
    }

    private function deviceType(string $userAgent): string
    {
        return match (true) {
            preg_match('/smart-tv|smarttv|tizen|web0s|hbbtv|appletv|googletv/i', $userAgent) === 1 => 'TV',
            preg_match('/ipad|tablet|kindle|silk\//i', $userAgent) === 1 => 'Tablet',
            preg_match('/mobile|android|iphone|ipod/i', $userAgent) === 1 => 'Mobil',
            default => 'Masaüstü',
        };
    }

    private function countryCode(Request $request): string
    {
        $country = strtoupper((string) ($request->header('CF-IPCountry') ?: $request->header('X-Appengine-Country') ?: ''));

        return preg_match('/^[A-Z]{2}$/', $country) === 1 ? $country : 'Bilinmiyor';
    }
}
