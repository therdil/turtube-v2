<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ShowChannelRequest;
use App\Http\Resources\Api\UserResource;
use App\Http\Resources\Api\VideoResource;
use App\Models\User;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;

class ChannelController extends Controller
{
    public function show(ShowChannelRequest $request, User $user): JsonResponse
    {
        abort_if($user->channel_visibility === 'private' && $request->user('sanctum')?->id !== $user->id, 404);
        $filters = $request->validated();
        $content = $filters['content'] ?? 'videos';

        $user->loadCount([
            'subscribers',
            'videos as public_videos_count' => fn (Builder $query) => $query->published()->where('is_short', false)->where('is_premium', false),
            'videos as public_shorts_count' => fn (Builder $query) => $query->published()->where('is_short', true)->where('is_premium', false),
        ]);

        $videos = $user->videos()
            ->published()
            ->where('is_premium', false)
            ->where('is_short', $content === 'shorts')
            ->with(['user', 'category'])
            ->withCount(['likes', 'comments'])
            ->when(
                $filters['search'] ?? null,
                fn (Builder $query, string $search) => app(VideoController::class)->filterSearch($query, $search),
            )
            ->latest()
            ->paginate($filters['limit'] ?? 20)
            ->withQueryString();

        $response = VideoResource::collection($videos)->response()->getData(true);

        return response()->json([
            ...$response,
            'channel' => (new UserResource($user))->resolve($request),
            'filters' => ['content' => $content],
        ]);
    }
}
