<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\User;
use App\Models\Video;
use Illuminate\Http\Response;

class SitemapController extends Controller
{
    public function __invoke(): Response
    {
        $videos = Video::published()->latest('updated_at')->get(['id', 'updated_at']);
        $categories = Category::query()->orderByDesc('updated_at')->get(['slug', 'updated_at']);
        $channels = User::query()
            ->whereHas('videos', fn ($query) => $query->published())
            ->latest('updated_at')
            ->get(['id', 'name', 'updated_at']);

        $urls = collect([
            route('home'),
            route('trending'),
            route('channels.index'),
            route('shorts.index'),
            route('live.index'),
        ])->map(fn ($url) => ['loc' => $url, 'lastmod' => now()])
            ->concat($videos->map(fn ($video) => [
                'loc' => route('videos.show', $video),
                'lastmod' => $video->updated_at,
            ]))
            ->concat($categories->map(fn ($category) => [
                'loc' => route('categories.show', $category),
                'lastmod' => $category->updated_at,
            ]))
            ->concat($channels->map(fn ($channel) => [
                'loc' => route('channels.show', $channel),
                'lastmod' => $channel->updated_at,
            ]));

        return response()
            ->view('sitemap', compact('urls'))
            ->header('Content-Type', 'application/xml');
    }
}
