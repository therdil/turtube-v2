<?php

namespace App\Http\Controllers;

use App\Models\Video;

class HomeController extends Controller
{
    public function index()
    {
        $videos = Video::latest()->get();

        return view('home', compact('videos'));
    }
}