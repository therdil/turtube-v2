<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\SearchRequest;
use App\Http\Resources\Api\VideoResource;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class SearchController extends Controller
{
    public function __invoke(SearchRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $search = trim($filters['q']);

        $videos = VideoController::publicVideos()
            ->when($filters['category'] ?? null, fn (Builder $query, string $category) => app(VideoController::class)->filterCategory($query, $category))
            ->tap(fn (Builder $query) => app(VideoController::class)->filterSearch($query, $search))
            ->when(
                ($filters['sort'] ?? 'relevance') === 'views',
                fn (Builder $query) => $query->orderByDesc('views')->latest(),
                fn (Builder $query) => $query->latest(),
            )
            ->paginate($filters['limit'] ?? 20)
            ->withQueryString();

        return VideoResource::collection($videos);
    }
}
