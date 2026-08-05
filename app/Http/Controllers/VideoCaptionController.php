<?php

namespace App\Http\Controllers;

use App\Models\Video;
use App\Models\VideoCaption;
use App\Jobs\ProcessVideoCaption;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class VideoCaptionController extends Controller
{
    public function store(Request $request, Video $video): RedirectResponse
    {
        $this->authorize('update', $video);

        $validated = $request->validate([
            'label' => ['required', 'string', 'max:80'],
            'language' => ['required', 'regex:/^[a-z]{2,3}(-[A-Z]{2})?$/'],
            'caption' => ['required', 'file', 'extensions:vtt', 'mimetypes:text/vtt,text/plain,application/octet-stream', 'max:5120'],
            'is_default' => ['nullable', 'boolean'],
        ]);

        if ($request->boolean('is_default')) {
            $video->captions()->update(['is_default' => false]);
        }

        $caption = $request->file('caption');
        $path = $caption->storeAs(
            'captions/'.$video->id,
            uniqid('', true).'.vtt',
            config('video.disk')
        );

        $caption = $video->captions()->create([
            'language' => $validated['language'],
            'label' => $validated['label'],
            'path' => $path,
            'is_default' => $request->boolean('is_default'),
        ]);

        ProcessVideoCaption::dispatch($caption);

        return back()->with('success', 'Altyazı yüklendi.');
    }

    public function destroy(Video $video, VideoCaption $caption): RedirectResponse
    {
        $this->authorize('update', $video);
        abort_unless($caption->video_id === $video->id, 404);

        Storage::disk(config('video.disk'))->delete($caption->path);
        $caption->delete();

        return back()->with('success', 'Altyazı silindi.');
    }

}
