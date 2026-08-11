<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ListVideosRequest;
use App\Http\Resources\Api\VideoResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class ShortController extends Controller
{
    public function index(ListVideosRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $category = $filters['category'] ?? null;
        $search = $filters['search'] ?? null;

        $shorts = VideoController::publicVideos()
            ->where('is_short', true)
            ->when($category, fn (Builder $query) => app(VideoController::class)->filterCategory($query, $category))
            ->when($search, fn (Builder $query) => app(VideoController::class)->filterSearch($query, $search))
            ->when(
                ($filters['sort'] ?? 'newest') === 'views',
                fn (Builder $query) => $query->orderByDesc('views')->latest(),
                fn (Builder $query) => $query->latest(),
            )
            ->paginate($filters['limit'] ?? 20)
            ->withQueryString();

        return VideoResource::collection($shorts);
    }
}
