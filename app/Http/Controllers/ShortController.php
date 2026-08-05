<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\View\View;

class ShortController extends Controller
{
    public function index(): View|\Illuminate\Http\JsonResponse
    {
        $shorts = Video::query()
            ->published()
            ->shorts()
            ->with(['user', 'category'])
            ->latest()
            ->paginate(12);

        if (request()->expectsJson()) {
            return response()->json([
                'data' => $shorts->getCollection()->map(fn (Video $short) => [
                    'url' => route('shorts.show', $short),
                    'title' => $short->title,
                    'thumbnail_url' => $short->thumbnail_url,
                    'channel' => $short->display_channel_name,
                    'views' => number_format($short->views),
                ])->values(),
                'next_page_url' => $shorts->nextPageUrl(),
            ]);
        }

        return view('shorts.index', compact('shorts'));
    }

    public function show(Video $video): View
    {
        abort_unless(
            $video->is_short && $video->isVisibleTo(auth()->user()),
            404
        );

        $nextShorts = Video::query()
            ->published()
            ->shorts()
            ->where('id', '!=', $video->id)
            ->with('user')
            ->orderByDesc('views')
            ->latest()
            ->take(8)
            ->get();

        return view('shorts.show', compact('video', 'nextShorts'));
    }
}
