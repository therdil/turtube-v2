<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoProgress;
use Illuminate\Http\Request;

class VideoProgressController extends Controller
{
    /**
     * Video izleme ilerlemesini kaydet
     */
    public function store(Request $request, Video $video)
    {
        $validated = $request->validate([
            'seconds' => 'required|integer|min:0',
            'percentage' => 'required|numeric|min:0|max:100',
        ]);

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

        return response()->json([
            'success' => true,
        ]);
    }
}