<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVideoRequest;
use App\Models\Video;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoController extends Controller
{
    public function create()
    {
        return view('videos.create');
    }

    public function store(StoreVideoRequest $request)
    {
        // Dosyaları kaydet
        $videoPath = $request->file('video')
            ->store('videos', 'public');

        $thumbnailPath = $request->file('thumbnail')
            ->store('thumbnails', 'public');

        // Veritabanına kaydet
        Video::create([
            'title' => $request->title,
            'description' => $request->description,
            'thumbnail' => $thumbnailPath,
            'video_path' => $videoPath,
            'channel_name' => auth()->user()->name,
            'user_id' => auth()->id(),
            'views' => 0,
            'duration' => 0,
        ]);

        return redirect('/')
            ->with('success', 'Video başarıyla yüklendi!');
    }

    public function show(Video $video)
    {
        // Görüntülenme sayısını artır
        $video->increment('views');

        // İzlenen video hariç son eklenen videolar
        $recommendedVideos = Video::where('id', '!=', $video->id)
            ->latest()
            ->take(8)
            ->get();

        return view('videos.show', compact('video', 'recommendedVideos'));
    }

    public function edit(Video $video)
    {
    // Kullanıcı sadece kendi videosunu düzenleyebilir.
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
        // Kullanıcı sadece kendi videosunu güncelleyebilir.
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
    // Güvenlik kontrolü
    if ($video->user_id !== auth()->id()) {
        abort(403);
    }

    // Thumbnail dosyasını sil
    if ($video->thumbnail && Storage::disk('public')->exists($video->thumbnail)) {
        Storage::disk('public')->delete($video->thumbnail);
    }

    // Video dosyasını sil
    if ($video->video_path && Storage::disk('public')->exists($video->video_path)) {
        Storage::disk('public')->delete($video->video_path);
    }

    // Veritabanındaki kaydı sil
    $video->delete();

    return redirect()
        ->route('videos.mine')
        ->with('success', 'Video başarıyla silindi.');
    }
}