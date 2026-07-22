<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVideoRequest;
use App\Models\Video;

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

    public function myVideos()
    {
        $videos = auth()->user()
            ->videos()
            ->latest()
            ->get();

        return view('videos.my-videos', compact('videos'));
    }
}