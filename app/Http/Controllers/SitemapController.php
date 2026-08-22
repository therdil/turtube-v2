<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\LiveStream;
use App\Models\User;
use App\Models\Video;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $videos = $this->publicIndexableVideos(Video::query())
            ->latest('updated_at')
            ->get(['id', 'updated_at', 'is_short']);

        $categories = Category::query()
            ->whereHas('videos', fn (Builder $query) => $this->publicIndexableVideos($query))
            ->orderByDesc('updated_at')
            ->get(['slug', 'updated_at']);

        $channels = User::query()
            ->where('channel_visibility', 'public')
            ->whereHas('videos', fn (Builder $query) => $this->publicIndexableVideos($query))
            ->latest('updated_at')
            ->get(['id', 'name', 'updated_at']);

        $liveStreams = LiveStream::query()
            ->whereIn('status', ['scheduled', 'live'])
            ->latest('updated_at')
            ->get(['id', 'updated_at']);

        $urls = collect([
            route('home'),
            route('trending'),
            route('channels.index'),
            route('shorts.index'),
            route('live.index'),
            route('premium.index'),
            route('privacy'),
            route('account.delete'),
        ])->map(fn (string $url) => ['loc' => $url])
            ->concat($videos->map(fn ($video) => [
                'loc' => $video->is_short
                    ? route('shorts.show', $video)
                    : route('videos.show', $video),
                'lastmod' => $video->updated_at,
            ]))
            ->concat($categories->map(fn ($category) => [
                'loc' => route('categories.show', $category),
                'lastmod' => $category->updated_at,
            ]))
            ->concat($channels->map(fn ($channel) => [
                'loc' => route('channels.show', $channel),
                'lastmod' => $channel->updated_at,
            ]))
            ->concat($liveStreams->map(fn ($stream) => [
                'loc' => route('live.show', $stream),
                'lastmod' => $stream->updated_at,
            ]))
            ->unique('loc')
            ->values();

        return response()
            ->view('sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml; charset=UTF-8');
    }

    private function publicIndexableVideos(Builder $query): Builder
    {
        return $query
            ->published()
            ->where('processing_status', 'ready')
            ->where('is_premium', false)
            ->whereNotNull('video_path');
    }
}
