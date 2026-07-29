<?php

namespace App\Http\Controllers;

use App\Models\Video;

class HomeController extends Controller
{
    public function index()
    {
        $videos = Video::query()
            ->with(['user', 'category'])
            ->latest()
            ->paginate(16);

        return view('home', compact('videos'));
    }
}
