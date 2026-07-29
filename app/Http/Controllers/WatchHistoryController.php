<?php

namespace App\Http\Controllers;

use App\Models\WatchHistory;
use Illuminate\Http\Request;

class WatchHistoryController extends Controller
{
    /**
     * İzleme geçmişini göster
     */
    public function index()
    {
        $history = WatchHistory::with([
                'video.user',
                'video.category',
            ])
            ->where('user_id', auth()->id())
            ->orderByDesc('watched_at')
            ->paginate(20);

        return view('history.index', compact('history'));
    }

    /**
     * İzleme geçmişini temizle
     */
    public function destroy()
    {
        WatchHistory::where('user_id', auth()->id())->delete();

        return redirect()
            ->route('history.index')
            ->with('success', 'İzleme geçmişiniz temizlendi.');
    }
}