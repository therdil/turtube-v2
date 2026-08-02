<?php

namespace App\Http\Controllers;

use App\Models\Video;

class HomeController extends Controller
{
    public function index()
    {
        $videos = Video::query()
            ->with([
                'user',
                'category',
            ])
            ->with(['progress' => function ($query) {

                if (auth()->check()) {

                    $query->where('user_id', auth()->id());

                }

            }])
            ->latest()
            ->paginate(16);

        return view('home', compact('videos'));
    }
}