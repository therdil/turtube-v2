<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Video;
use Illuminate\View\View;

class ExploreController extends Controller
{
    /**
     * En çok izlenen videoları göster.
     */
    public function trending(): View
    {
        $videos = Video::query()
            ->with(['user', 'category'])
            ->orderByDesc('views')
            ->latest()
            ->paginate(16);

        return view('explore-trending', compact('videos'));
    }

    /**
     * Platformdaki kanalları keşfet.
     */
    public function channels(): View
    {
        $channels = User::query()
            ->withCount(['videos', 'subscribers'])
            ->withSum('videos', 'views')
            ->orderByDesc('subscribers_count')
            ->orderByDesc('videos_count')
            ->orderBy('name')
            ->paginate(24);

        return view('explore-channels', compact('channels'));
    }
}
