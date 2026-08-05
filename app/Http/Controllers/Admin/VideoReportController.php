<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\VideoReport;
use App\Services\AdminActivityLogger;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Validation\Rule;

class VideoReportController extends Controller
{
    public function __construct(private AdminActivityLogger $activityLogger)
    {
    }

    public function index(Request $request): View
    {
        $reports = VideoReport::query()
            ->with(['video.user', 'reporter', 'reviewer'])
            ->when(
                $request->filled('status') && array_key_exists($request->string('status')->toString(), VideoReport::STATUSES),
                fn ($query) => $query->where('status', $request->string('status')->toString())
            )
            ->latest()
            ->paginate(20)
            ->withQueryString();

        return view('admin.reports', compact('reports'));
    }

    public function update(Request $request, VideoReport $report): RedirectResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(VideoReport::STATUSES))],
        ]);

        $report->update([
            'status' => $validated['status'],
            'reviewed_by' => $request->user()->id,
            'reviewed_at' => now(),
        ]);
        $this->activityLogger->record($request->user(), 'report.reviewed', 'Icerik raporu incelendi.', $report, ['status' => $validated['status'], 'video_id' => $report->video_id]);

        return back()->with('success', 'Rapor durumu güncellendi.');
    }
}
