<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\ListVideosRequest;
use App\Http\Resources\Api\VideoResource;
use App\Models\Video;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;

class VideoController extends Controller
{
    public function index(ListVideosRequest $request): AnonymousResourceCollection
    {
        $filters = $request->validated();
        $category = $filters['category'] ?? null;
        $search = $filters['search'] ?? null;

        $videos = $this->publicVideos()
            ->where('is_short', false)
            ->when($category, fn (Builder $query) => $this->filterCategory($query, $category))
            ->when($search, fn (Builder $query) => $this->filterSearch($query, $search))
            ->when(
                ($filters['sort'] ?? 'newest') === 'views',
                fn (Builder $query) => $query->orderByDesc('views')->latest(),
                fn (Builder $query) => $query->latest(),
            )
            ->paginate($filters['limit'] ?? 20)
            ->withQueryString();

        return VideoResource::collection($videos);
    }

    public function show(Video $video): VideoResource
    {
        abort_unless($video->status === 'public' && ! $video->is_short, 404);
        abort_if($video->is_premium, 403, 'Premium content requires an authenticated premium session.');

        $video->load(['user', 'category'])->loadCount(['likes', 'comments']);

        return new VideoResource($video);
    }

    public static function publicVideos(): Builder
    {
        return Video::query()
            ->published()
            ->where('is_premium', false)
            ->with(['user', 'category'])
            ->withCount(['likes', 'comments']);
    }

    public function filterCategory(Builder $query, string $category): Builder
    {
        return $query->whereHas('category', function (Builder $categoryQuery) use ($category) {
            ctype_digit($category)
                ? $categoryQuery->whereKey((int) $category)
                : $categoryQuery->where('slug', $category);
        });
    }

    public function filterSearch(Builder $query, string $search): Builder
    {
        $term = '%'.str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], trim($search)).'%';

        return $query->where(function (Builder $searchQuery) use ($term) {
            $searchQuery
                ->where('title', 'like', $term)
                ->orWhere('description', 'like', $term)
                ->orWhere('channel_name', 'like', $term);
        });
    }
}
