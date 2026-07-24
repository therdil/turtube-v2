<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVideoRequest;
use App\Models\Video;
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
        return view('videos.create');
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

        $video->load([
            'comments.user',
        ]);

        // Toplam beğeni sayısını yükle
        $video->loadCount('likes');

        // Giriş yapan kullanıcı bu videoyu beğenmiş mi?
        $isLiked = false;

        if (auth()->check()) {
            $isLiked = $video->likes()
                ->where('user_id', auth()->id())
                ->exists();
        }

        $recommendedVideos = Video::where('id', '!=', $video->id)
            ->latest()
            ->take(8)
            ->get();

        return view('videos.show', [
            'video' => $video,
            'recommendedVideos' => $recommendedVideos,
            'isLiked' => $isLiked,
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