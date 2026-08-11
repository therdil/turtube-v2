<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVideoReportRequest;
use App\Models\Video;
use App\Models\VideoReport;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class VideoReportController extends Controller
{
    public function store(StoreVideoReportRequest $request, Video $video): JsonResponse|RedirectResponse
    {
        abort_unless($video->isVisibleTo($request->user()), 404);
        abort_unless($video->isPremiumAccessibleTo($request->user()), 404);
        $this->authorize('report', $video);

        VideoReport::updateOrCreate(
            [
                'video_id' => $video->id,
                'reporter_id' => $request->user()->id,
            ],
            [
                'reason' => $request->validated('reason'),
                'details' => $request->validated('details'),
                'status' => 'open',
                'reviewed_by' => null,
                'reviewed_at' => null,
            ]
        );

        if ($request->expectsJson()) {
            return response()->json([
                'success' => true,
                'message' => 'Raporunuz alındı.',
            ]);
        }

        return back()->with('success', 'Raporunuz incelenmek üzere yönetim ekibine iletildi.');
    }
}
