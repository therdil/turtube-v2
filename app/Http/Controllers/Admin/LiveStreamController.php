<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LiveStream;
use App\Services\AdminActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class LiveStreamController extends Controller
{
    public function __construct(private AdminActivityLogger $activityLogger)
    {
    }

    public function index(Request $request): View
    {
        $validated = $request->validate([
            'status' => ['nullable', 'in:scheduled,live,ended'],
        ]);

        $streams = LiveStream::query()
            ->with('user')
            ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->latest()
            ->paginate(25)
            ->withQueryString();

        return view('admin.live-streams', compact('streams'));
    }

    public function end(LiveStream $stream): RedirectResponse
    {
        if ($stream->status !== 'ended') {
            $stream->update([
                'status' => 'ended',
                'ended_at' => now(),
            ]);
            $this->activityLogger->record(auth()->user(), 'live_stream.ended', 'Canli yayin yonetici tarafindan sonlandirildi.', $stream);
        }

        return back()->with('success', 'Yayın sonlandırıldı.');
    }
}
