<?php

namespace App\Http\Controllers;

use App\Models\LiveStream;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Illuminate\View\View;

class LiveStreamController extends Controller
{
    public function index(): View
    {
        $streams = LiveStream::query()
            ->with('user')
            ->whereIn('status', ['scheduled', 'live'])
            ->orderByRaw("status = 'live' desc")
            ->orderBy('scheduled_at')
            ->paginate(12);

        return view('live.index', compact('streams'));
    }

    public function show(LiveStream $stream): View
    {
        abort_unless(
            in_array($stream->status, ['scheduled', 'live'], true)
                || $stream->user_id === auth()->id()
                || auth()->user()?->is_admin,
            404
        );

        $stream->load('user');

        return view('live.show', compact('stream'));
    }

    public function create(): View
    {
        return view('live.create');
    }

    public function store(Request $request): RedirectResponse
    {
        $this->authorize('create', LiveStream::class);

        $validated = $request->validate([
            'title' => ['required', 'string', 'max:255'],
            'description' => ['nullable', 'string', 'max:3000'],
            'scheduled_at' => ['nullable', 'date', 'after:now'],
            'stream_url' => ['nullable', 'url', 'max:2048'],
        ]);

        $stream = auth()->user()->liveStreams()->create([
            ...$validated,
            'stream_key' => Str::random(40),
            'status' => 'scheduled',
        ]);

        return redirect()->route('live.show', $stream)
            ->with('success', 'Canlı yayın taslağı oluşturuldu.');
    }

    public function start(LiveStream $stream): RedirectResponse
    {
        $this->authorize('manage', $stream);
        abort_unless($stream->status === 'scheduled', 422, 'Yalnızca planlanmış yayınlar başlatılabilir.');

        $stream->update([
            'status' => 'live',
            'started_at' => now(),
            'ended_at' => null,
        ]);

        return back()->with('success', 'Canlı yayın başlatıldı.');
    }

    public function stop(LiveStream $stream): RedirectResponse
    {
        $this->authorize('manage', $stream);
        abort_unless($stream->status === 'live', 422, 'Yalnızca aktif yayınlar sonlandırılabilir.');

        $stream->update([
            'status' => 'ended',
            'ended_at' => now(),
        ]);

        return back()->with('success', 'Canlı yayın sonlandırıldı.');
    }

}
