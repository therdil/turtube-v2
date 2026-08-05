<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoProgress;
use App\Services\AnalyticsService;
use Illuminate\Http\Request;

class VideoProgressController extends Controller
{
    /**
     * Video izleme ilerlemesini kaydet
     */
    public function store(Request $request, Video $video, AnalyticsService $analyticsService)
    {
        abort_unless(
            $video->isVisibleTo(auth()->user()) && $video->isPremiumAccessibleTo(auth()->user()),
            404
        );

        $validated = $request->validate([
            'seconds' => 'required|integer|min:0',
            'percentage' => 'required|numeric|min:0|max:100',
        ]);

        $progress = VideoProgress::query()
            ->where('user_id', auth()->id())
            ->where('video_id', $video->id)
            ->first();

        $previousSeconds = $progress?->seconds ?? 0;
        $watchedSeconds = max(0, min(15, $validated['seconds'] - $previousSeconds));

        VideoProgress::updateOrCreate(

            [
                'user_id' => auth()->id(),
                'video_id' => $video->id,
            ],

            [
                'seconds' => $validated['seconds'],
                'percentage' => $validated['percentage'],
            ]

        );

        $analyticsService->recordWatchTime($video, $watchedSeconds);

        return response()->json([
            'success' => true,
        ]);
    }
}
