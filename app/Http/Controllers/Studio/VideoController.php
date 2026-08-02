<?php

namespace App\Http\Controllers\Studio;

use App\Http\Controllers\Controller;
use App\Models\Video;
use Illuminate\Support\Facades\Auth;

class VideoController extends Controller
{
    public function index()
    {
        $videos = Video::query()
            ->with([
                'category',
            ])
            ->withCount([
                'likes',
                'comments',
            ])
            ->where('user_id', Auth::id())
            ->latest()
            ->paginate(15);

        return view('studio.videos.index', compact('videos'));
    }
}