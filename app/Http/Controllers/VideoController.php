<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVideoRequest;
use App\Http\Requests\UpdateVideoRequest;
use App\Jobs\ProcessUploadedVideo;
use App\Models\Category;
use App\Models\Subscription;
use App\Models\Video;
use App\Models\VideoProgress;
use App\Models\WatchHistory;
use App\Services\AnalyticsService;
use App\Services\VideoDeletionService;
use App\Services\ContentCache;
use App\Services\VideoProcessingService;
use App\Services\VideoRecommendationService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    protected AnalyticsService $analyticsService;

    public function __construct(
        AnalyticsService $analyticsService,
        protected VideoDeletionService $videoDeletionService,
        protected VideoRecommendationService $recommendationService
    ) {
        $this->analyticsService = $analyticsService;
    }

    public function create()
    {
        $this->authorize('create', Video::class);

        $categories = Category::orderBy('name')->get();

        $uploadDefaults = [
            'description' => auth()->user()->default_video_description,
            'status' => auth()->user()->default_video_status ?: 'public',
            'license' => auth()->user()->default_video_license ?: 'standard',
        ];

        return view('videos.create', compact('categories', 'uploadDefaults'));
    }

    public function store(StoreVideoRequest $request, VideoProcessingService $videoProcessing)
    {
        $this->authorize('create', Video::class);

        $videoPath = $request->file('video')->store('videos', config('video.disk'));
        $tags = collect($request->input('tags', []))
            ->map(fn ($tag) => trim((string) $tag))
            ->filter()
            ->unique()
            ->values()
            ->all();

        $thumbnailPath = null;

        try {
            if ($videoProcessing->isAvailable()) {
                $thumbnailPath = $videoProcessing->generateThumbnail(
                    $videoPath,
                    $request->boolean('is_short'),
                );
            }
        } catch (\Throwable $exception) {
            report($exception);
        }

        try {
            $video = Video::create([
                'title'        => $request->title,
                'description'  => $request->description,
                'video_path'   => $videoPath,
                'thumbnail'    => $thumbnailPath,
                'processing_status' => 'pending',
                'channel_name' => auth()->user()->name,
                'user_id'      => auth()->id(),
                'category_id'  => $request->category_id,
                'status'       => $request->status,
                'license'      => $request->license,
                'tags'         => $tags,
                'is_short'     => $request->boolean('is_short'),
                'is_premium'   => $request->boolean('is_premium'),
                'views'        => 0,
                'duration'     => 0,
            ]);
        } catch (\Throwable $exception) {
            Storage::disk(config('video.disk'))->delete(array_filter([$videoPath, $thumbnailPath]));

            throw $exception;
        }

        ProcessUploadedVideo::dispatch($video);
        ContentCache::flush();

        return redirect()
            ->route('videos.mine')
            ->with('success', 'Video yüklendi. Medya işlemleri arka planda devam ediyor.');
    }

    public function show(Request $request, Video $video)
    {
        abort_unless($video->isVisibleTo(auth()->user()), 404);
        abort_unless($video->isPremiumAccessibleTo(auth()->user()), 403);

        if ($video->age_restriction > 0 && ! $request->session()->get('age-confirmed-videos.'.$video->id)) {
            return response()->view('videos.age-gate', compact('video'));
        }

        $this->analyticsService->recordView($video, $request);

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
            'comments' => fn ($query) => $query
                ->whereNull('parent_id')
                ->with('user', 'replies.user')
                ->orderByDesc('is_pinned')
                ->latest(),
            'chapters',
            'captions',
        ]);

        $video->loadCount('likes');

        $ratingSummary = $video->ratings()
            ->selectRaw('AVG(rating) as average_rating, COUNT(*) as ratings_count')
            ->first();

        $isLiked = false;
        $isSubscribed = false;
        $isWatchLater = false;
        $isFavorited = false;
        $userRating = null;
        $progressSeconds = 0;
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

            $isFavorited = auth()->user()
                ->favoriteVideos()
                ->whereKey($video->id)
                ->exists();

            $userRating = $video->ratings()
                ->where('user_id', auth()->id())
                ->value('rating');

            $progress = VideoProgress::where('user_id', auth()->id())
                ->where('video_id', $video->id)
                ->first();

            if ($progress) {
                $progressSeconds = $progress->seconds;
            }

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

        $recommendedVideos = $this->recommendationService->forVideo($video, auth()->user());

        return view('videos.show', [
            'video' => $video,
            'recommendedVideos' => $recommendedVideos,
            'isLiked' => $isLiked,
            'isSubscribed' => $isSubscribed,
            'isWatchLater' => $isWatchLater,
            'isFavorited' => $isFavorited,
            'userRating' => $userRating,
            'ratingAverage' => round((float) ($ratingSummary->average_rating ?? 0), 1),
            'ratingsCount' => (int) ($ratingSummary->ratings_count ?? 0),
            'playlists' => $playlists,
            'playlistVideoIds' => $playlistVideoIds,
            'subscribersCount' => $subscribersCount,
            'progressSeconds' => $progressSeconds,
        ]);
    }

    public function edit(Video $video)
    {
        $this->authorize('update', $video);

        $categories = Category::orderBy('name')->get();

        $video->load(['chapters', 'captions']);

        return view('videos.edit', compact('video', 'categories'));
    }

    public function myVideos()
    {
        $videos = auth()->user()
            ->videos()
            ->latest()
            ->get();

        return view('videos.my-videos', compact('videos'));
    }

    public function confirmAge(Request $request, Video $video)
    {
        abort_unless($video->isVisibleTo(auth()->user()), 404);

        $request->validate(['confirmed' => ['accepted']]);
        $request->session()->put('age-confirmed-videos.'.$video->id, true);

        return redirect()->route('videos.show', $video);
    }
    public function update(UpdateVideoRequest $request, Video $video)
    {
        $this->authorize('update', $video);

        $validated = $request->validated();

        /*
        |--------------------------------------------------------------------------
        | Thumbnail Güncelle
        |--------------------------------------------------------------------------
        */

        if ($request->hasFile('thumbnail')) {

            if (
                $video->thumbnail &&
                Storage::disk(config('video.disk'))->exists($video->thumbnail)
            ) {
                Storage::disk(config('video.disk'))->delete($video->thumbnail);
            }

            $validated['thumbnail'] = $request
                ->file('thumbnail')
                ->store('thumbnails', config('video.disk'));
        }

        $video->update($validated);
        ContentCache::flush();

        return redirect()
            ->route('videos.mine')
            ->with('success', 'Video başarıyla güncellendi.');
    }

    public function destroy(Video $video)
    {
        $this->authorize('delete', $video);

        $this->videoDeletionService->delete($video);
        ContentCache::flush();

        return redirect()
            ->route('videos.mine')
            ->with('success', 'Video başarıyla silindi.');
    }
}
