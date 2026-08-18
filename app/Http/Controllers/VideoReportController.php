<?php

namespace App\Http\Controllers;

use App\Http\Requests\StoreVideoReportRequest;
use App\Http\Resources\Api\VideoReportResource;
use App\Models\User;
use App\Models\Video;
use App\Models\VideoReport;
use App\Notifications\AdminVideoReportNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;

class VideoReportController extends Controller
{
    public function store(StoreVideoReportRequest $request, Video $video): JsonResponse|RedirectResponse
    {
        abort_unless($video->isVisibleTo($request->user()), 404);
        abort_unless($video->isPremiumAccessibleTo($request->user()), 404);
        $this->authorize('report', $video);

        $report = VideoReport::firstOrCreate([
            'video_id' => $video->id,
            'reporter_id' => $request->user()->id,
        ], [
            'reason' => $request->validated('reason'),
            'details' => $request->validated('details'),
            'status' => 'open',
        ]);

        if (! $report->wasRecentlyCreated) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Bu video için zaten bir şikayet gönderdiniz.'], 409);
            }

            return back()->with('error', 'Bu video için zaten bir şikayet gönderdiniz.');
        }

        User::query()->where('is_admin', true)->each(
            fn (User $admin) => $admin->notify(new AdminVideoReportNotification($report))
        );

        if ($request->expectsJson()) {
            return (new VideoReportResource($report->load(['video.user', 'reporter'])))
                ->additional(['message' => 'Şikayetiniz başarıyla gönderildi.'])
                ->response()
                ->setStatusCode(201);
        }

        return back()->with('success', 'Şikayetiniz yönetim ekibine iletildi.');
    }
}
