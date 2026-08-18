<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\FeedbackResource;
use App\Models\Feedback;
use App\Services\AdminActivityLogger;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class FeedbackController extends Controller
{
    public function __construct(private readonly AdminActivityLogger $activityLogger) {}

    public function index(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate(['status' => ['nullable', Rule::in(Feedback::STATUSES)], 'q' => ['nullable', 'string', 'max:100'], 'limit' => ['nullable', 'integer', 'min:1', 'max:50']]);
        return FeedbackResource::collection(Feedback::query()->with('user')
            ->when($filters['status'] ?? null, fn ($query, $status) => $query->where('status', $status))
            ->when(filled($filters['q'] ?? null), fn ($query) => $query->where(fn ($items) => $items->where('subject', 'like', '%'.$filters['q'].'%')->orWhere('message', 'like', '%'.$filters['q'].'%')))
            ->latest()->paginate($filters['limit'] ?? 20)->withQueryString());
    }

    public function update(Request $request, Feedback $feedback): JsonResponse
    {
        $data = $request->validate(['status' => ['sometimes', Rule::in(Feedback::STATUSES)], 'admin_note' => ['sometimes', 'nullable', 'string', 'max:5000']]);
        $data['reviewed_by'] = $request->user('sanctum')->id;
        $data['reviewed_at'] = now();
        $feedback->update($data);
        $this->activityLogger->record($request->user('sanctum'), 'feedback.reviewed', 'Geri bildirim incelendi.', $feedback, $data);
        return (new FeedbackResource($feedback->fresh()->load('user')))->additional(['message' => 'Geri bildirim güncellendi.'])->response();
    }
}
