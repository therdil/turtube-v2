<?php

namespace App\Http\Controllers;

use App\Models\Video;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class VideoRatingController extends Controller
{
    public function store(Request $request, Video $video): JsonResponse
    {
        abort_unless(
            $video->isVisibleTo($request->user()) && $video->isPremiumAccessibleTo($request->user()),
            404
        );

        $validated = $request->validate([
            'rating' => ['required', 'integer', 'between:1,5'],
        ]);

        $video->ratings()->updateOrCreate(
            ['user_id' => $request->user()->id],
            ['rating' => $validated['rating']]
        );

        return response()->json([
            'rating' => $validated['rating'],
            'average' => round((float) $video->ratings()->avg('rating'), 1),
            'count' => $video->ratings()->count(),
        ]);
    }
}
