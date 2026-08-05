<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoChapter;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class VideoChapterController extends Controller
{
    public function store(Request $request, Video $video): RedirectResponse
    {
        $this->authorize('update', $video);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:120'],
            'start_seconds' => ['required', 'integer', 'min:0', 'max:172800'],
        ]);

        abort_if(
            ! $video->chapters()->where('start_seconds', $validated['start_seconds'])->exists()
                && $video->chapters()->count() >= 100,
            422,
            'Bir videoya en fazla 100 bölüm eklenebilir.'
        );

        VideoChapter::updateOrCreate(
            ['video_id' => $video->id, 'start_seconds' => $validated['start_seconds']],
            ['title' => $validated['title']]
        );

        return back()->with('success', 'Bölüm kaydedildi.');
    }

    public function destroy(Video $video, VideoChapter $chapter): RedirectResponse
    {
        $this->authorize('update', $video);
        abort_unless($chapter->video_id === $video->id, 404);

        $chapter->delete();

        return back()->with('success', 'Bölüm silindi.');
    }

}
