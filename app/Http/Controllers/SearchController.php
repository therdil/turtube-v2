<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\Request;

class SearchController extends Controller
{
    /**
     * Arama sonuçlarını göster.
     */
    public function index(Request $request)
    {
        $query = trim($request->input('q', ''));

        $videos = Video::query()
            ->when($query !== '', function ($builder) use ($query) {
                $builder->where('title', 'like', "%{$query}%")
                        ->orWhere('description', 'like', "%{$query}%")
                        ->orWhere('channel_name', 'like', "%{$query}%");
            })
            ->latest()
            ->paginate(16)
            ->withQueryString();

        return view('search.index', [
            'query' => $query,
            'videos' => $videos,
        ]);
    }
}