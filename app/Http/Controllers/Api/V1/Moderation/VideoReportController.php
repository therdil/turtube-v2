<?php

namespace App\Http\Controllers\Api\V1\Moderation;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\VideoReportResource;
use App\Models\VideoReport;
use App\Services\AdminActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class VideoReportController extends Controller
{
    public function __construct(private readonly AdminActivityLogger $activityLogger)
    {
    }

    public function index(Request $request): AnonymousResourceCollection
    {
        $validated = $request->validate([
            'status' => ['nullable', Rule::in(array_keys(VideoReport::STATUSES))],
            'limit' => ['nullable', 'integer', 'min:1', 'max:50'],
        ]);

        return VideoReportResource::collection(
            VideoReport::query()
                ->with(['reporter', 'video.user'])
                ->when($validated['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
                ->latest()
                ->paginate($validated['limit'] ?? 20)
                ->withQueryString()
        );
    }

    public function update(Request $request, VideoReport $report): JsonResponse
    {
        $validated = $request->validate([
            'status' => ['required', Rule::in(array_keys(VideoReport::STATUSES))],
        ]);

        $report->update([
            'status' => $validated['status'],
            'reviewed_by' => $request->user('sanctum')->id,
            'reviewed_at' => now(),
        ]);
        $this->activityLogger->record($request->user('sanctum'), 'report.reviewed', 'İçerik raporu incelendi.', $report, $validated);

        return (new VideoReportResource($report->load(['reporter', 'video.user'])))
            ->additional(['message' => 'Rapor durumu güncellendi.'])
            ->response();
    }
}
