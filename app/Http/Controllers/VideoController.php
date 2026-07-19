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
    // 1. Dosyaları kaydet
    $videoPath = $request->file('video')
        ->store('videos', 'public');

    $thumbnailPath = $request->file('thumbnail')
        ->store('thumbnails', 'public');

    // 2. Veritabanına kaydet
    Video::create([
        'title' => $request->title,
        'description' => $request->description,
        'thumbnail' => $thumbnailPath,
        'video_path' => $videoPath,
        'channel_name' => 'Erdil',
        'views' => 0,
        'duration' => 0,
    ]);

    // 3. Ana sayfaya dön
    return redirect('/')
        ->with('success', 'Video başarıyla yüklendi!');
}

    public function show(Video $video)
    {
        return view('videos.show', compact('video'));
    }
}