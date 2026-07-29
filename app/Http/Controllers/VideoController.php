<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVideoRequest;
use App\Models\Category;
use App\Models\Subscription;
use App\Models\Video;
use App\Models\WatchHistory;
use App\Services\VideoProcessingService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    protected VideoProcessingService $videoService;

    public function __construct(VideoProcessingService $videoService)
    {
        $this->videoService = $videoService;
    }

    public function create()
    {
        $categories = Category::orderBy('name')->get();

        return view('videos.create', compact('categories'));
    }

    public function store(StoreVideoRequest $request)
    {
        $videoPath = $request->file('video')->store('videos', 'public');

        $thumbnailPath = $this->videoService->generateThumbnail($videoPath);

        $previewPath = $this->videoService->generatePreview($videoPath);

        $duration = $this->videoService->getDuration($videoPath);

        Video::create([
            'title'        => $request->title,
            'description'  => $request->description,
            'thumbnail'    => $thumbnailPath,
            'preview'      => $previewPath,
            'video_path'   => $videoPath,
            'channel_name' => auth()->user()->name,
            'user_id'      => auth()->id(),
            'category_id'  => $request->category_id,
            'views'        => 0,
            'duration'     => gmdate('i:s', $duration),
        ]);

        return redirect()
            ->route('videos.mine')
            ->with('success', 'Video başarıyla yüklendi!');
    }

    public function show(Video $video)
    {
        $video->increment('views');

        if (auth()->check()) {

            WatchHistory::updateOrCreate(

                [
                    'user_id'  => auth()->id(),
                    'video_id' => $video->id,
                ],

                [
                    'watched_at' => now(),
                ]

            );

        }

        $video->load([
            'user',
            'category',
            'comments.user',
        ]);

        $video->loadCount('likes');

$isLiked = false;
$isSubscribed = false;
$isWatchLater = false;
$playlists = collect();
$playlistVideoIds = collect();

if (auth()->check()) {

    $isLiked = $video->likes()
        ->where('user_id', auth()->id())
        ->exists();

    $isSubscribed = Subscription::where('subscriber_id', auth()->id())
        ->where('channel_id', $video->user_id)
        ->exists();

    $isWatchLater = auth()->user()
        ->watchLaterVideos()
        ->where('video_id', $video->id)
        ->exists();

    $playlists = auth()->user()
        ->playlists()
        ->orderBy('name')
        ->get();

    $playlistVideoIds = $video->playlists()
        ->where('user_id', auth()->id())
        ->pluck('playlists.id');
}

$subscribersCount = Subscription::where(
    'channel_id',
    $video->user_id
)->count();

        $recommendedVideos = Video::query()
    ->where('id', '!=', $video->id)
    ->when($video->category_id, function ($query) use ($video) {
        $query->where('category_id', $video->category_id);
    })
    ->orderByDesc('views')
    ->latest()
    ->take(8)
    ->get();

if ($recommendedVideos->count() < 8) {

    $missing = 8 - $recommendedVideos->count();

    $additionalVideos = Video::query()
        ->where('id', '!=', $video->id)
        ->whereNotIn('id', $recommendedVideos->pluck('id'))
        ->latest()
        ->take($missing)
        ->get();

    $recommendedVideos = $recommendedVideos->concat($additionalVideos);

}

        return view('videos.show', [
            'video' => $video,
            'recommendedVideos' => $recommendedVideos,
            'isLiked' => $isLiked,
            'isSubscribed' => $isSubscribed,
            'isWatchLater' => $isWatchLater,
            'playlists' => $playlists,
            'playlistVideoIds' => $playlistVideoIds,
            'subscribersCount' => $subscribersCount,
        ]);
    }

    public function edit(Video $video)
    {
        if ($video->user_id !== auth()->id()) {
            abort(403);
        }

        return view('videos.edit', compact('video'));
    }

    public function myVideos()
    {
        $videos = auth()->user()
            ->videos()
            ->latest()
            ->get();

        return view('videos.my-videos', compact('videos'));
    }

    public function update(Request $request, Video $video)
    {
        if ($video->user_id !== auth()->id()) {
            abort(403);
        }

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
        ]);

        $video->update($validated);

        return redirect()
            ->route('videos.mine')
            ->with('success', 'Video başarıyla güncellendi.');
    }

    public function destroy(Video $video)
    {
        if ($video->user_id !== auth()->id()) {
            abort(403);
        }

        if ($video->thumbnail && Storage::disk('public')->exists($video->thumbnail)) {
            Storage::disk('public')->delete($video->thumbnail);
        }

        if ($video->preview && Storage::disk('public')->exists($video->preview)) {
            Storage::disk('public')->delete($video->preview);
        }

        if ($video->video_path && Storage::disk('public')->exists($video->video_path)) {
            Storage::disk('public')->delete($video->video_path);
        }

        $video->delete();

        return redirect()
            ->route('videos.mine')
            ->with('success', 'Video başarıyla silindi.');
    }
}
