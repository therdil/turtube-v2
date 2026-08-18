<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\FeedbackResource;
use App\Models\Feedback;
use App\Models\User;
use App\Notifications\AdminFeedbackNotification;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class FeedbackController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        $limit = $request->validate(['limit' => ['nullable', 'integer', 'min:1', 'max:50']])['limit'] ?? 20;

        return FeedbackResource::collection(
            $request->user('sanctum')->feedback()
                ->latest()->paginate($limit)->withQueryString()
        );
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(Feedback::TYPES)],
            'subject' => ['required', 'string', 'max:160'],
            'message' => ['required', 'string', 'min:5', 'max:5000'],
        ]);
        $feedback = $request->user('sanctum')->feedback()->create($data);
        User::query()->where('is_admin', true)->each(fn (User $admin) => $admin->notify(new AdminFeedbackNotification($feedback)));

        return (new FeedbackResource($feedback->load('user')))
            ->additional(['message' => 'Geri bildiriminiz başarıyla gönderildi.'])
            ->response()->setStatusCode(201);
    }
}
