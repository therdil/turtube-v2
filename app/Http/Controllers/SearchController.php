<?php

namespace App\Http\Controllers;

use App\Models\Category;
use App\Models\SearchHistory;
use App\Models\Video;
use App\Services\ContentCache;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Schema;

class SearchController extends Controller
{
    public function index(Request $request)
    {
        $validated = $request->validate([
            'q' => ['nullable', 'string', 'max:100'],
            'category_id' => ['nullable', 'integer', 'exists:categories,id'],
            'duration' => ['nullable', 'in:any,short,medium,long'],
            'hd' => ['nullable', 'boolean'],
            'premium' => ['nullable', 'in:any,yes,no'],
            'shorts' => ['nullable', 'in:any,yes,no'],
            'date' => ['nullable', 'in:any,today,week,month,year'],
            'sort' => ['nullable', 'in:relevance,newest,views'],
        ]);
        $query = trim($validated['q'] ?? '');
        $page = max(1, (int) $request->input('page', 1));
        $filters = collect($validated)->except('q')->filter(fn ($value) => $value !== null && $value !== 'any')->all();
        $hasSearchHistory = Schema::hasTable('search_histories');

        $videos = $query !== '' && auth()->guest()
            ? ContentCache::remember('search', sha1(mb_strtolower($query).':'.json_encode($filters)).':page:'.$page, 90, fn () => $this->query($query, $filters)->paginate(16))
            : $this->query($query, $filters)->paginate(16);

        if ($query !== '' && $page === 1 && $hasSearchHistory) {
            $this->recordSearch($query, $request);
        }

        $recentSearches = auth()->check() && $hasSearchHistory
            ? SearchHistory::query()
                ->where('user_id', auth()->id())
                ->latest('searched_at')
                ->get()
                ->unique('normalized_query')
                ->take(8)
                ->values()
            : collect();
        $trendingSearches = $hasSearchHistory ? SearchHistory::query()
            ->where('searched_at', '>=', now()->subDays(28))
            ->selectRaw('MAX(query) as query, normalized_query, COUNT(*) as searches')
            ->groupBy('normalized_query')
            ->orderByDesc('searches')
            ->limit(8)
            ->get() : collect();
        $categories = Category::query()->orderBy('name')->get(['id', 'name']);

        return view('search.index', compact('query', 'videos', 'filters', 'recentSearches', 'trendingSearches', 'categories'));
    }

    public function suggestions(Request $request): JsonResponse
    {
        $validated = $request->validate(['q' => ['required', 'string', 'min:2', 'max:100']]);
        $term = trim($validated['q']);

        $videos = $this->query($term, ['sort' => 'relevance'])
            ->take(5)
            ->get(['id', 'title', 'thumbnail', 'user_id', 'views']);
        $trends = Schema::hasTable('search_histories')
            ? SearchHistory::query()
                ->where('normalized_query', 'like', '%'.mb_strtolower($term).'%')
                ->selectRaw('MAX(query) as query, normalized_query, COUNT(*) as searches')
                ->groupBy('normalized_query')
                ->orderByDesc('searches')
                ->limit(4)
                ->get()
            : collect();

        return response()->json([
            'videos' => $videos->map(fn (Video $video) => [
                'title' => $video->title,
                'url' => route('videos.show', $video),
                'thumbnail' => $video->thumbnail_url,
                'views' => number_format($video->views),
            ]),
            'queries' => $trends->pluck('query')->values(),
        ]);
    }

    private function query(string $term, array $filters = [])
    {
        $query = Video::query()
            ->published()
            ->with(['user', 'category'])
            ->when($term !== '', function ($builder) use ($term) {
                $builder->where(function ($search) use ($term) {
                    $search->where('title', 'like', "%{$term}%")
                        ->orWhere('description', 'like', "%{$term}%")
                        ->orWhere('channel_name', 'like', "%{$term}%");
                });
            });

        if (isset($filters['category_id'])) $query->where('category_id', $filters['category_id']);
        if (($filters['duration'] ?? null) === 'short') $query->where('duration', '<', 240);
        if (($filters['duration'] ?? null) === 'medium') $query->whereBetween('duration', [240, 1200]);
        if (($filters['duration'] ?? null) === 'long') $query->where('duration', '>', 1200);
        if (($filters['hd'] ?? false)) {
            $query->where(function ($hd) {
                foreach (['720p', '1080p', '1440p', '2160p'] as $quality) {
                    $hd->orWhere('video_qualities', 'like', '%'.$quality.'%');
                }
            });
        }
        if (($filters['premium'] ?? null) === 'yes') $query->where('is_premium', true);
        if (($filters['premium'] ?? null) === 'no') $query->where('is_premium', false);
        if (($filters['shorts'] ?? null) === 'yes') $query->where('is_short', true);
        if (($filters['shorts'] ?? null) === 'no') $query->where('is_short', false);
        if (($filters['date'] ?? null) === 'today') $query->where('created_at', '>=', now()->startOfDay());
        if (($filters['date'] ?? null) === 'week') $query->where('created_at', '>=', now()->subWeek());
        if (($filters['date'] ?? null) === 'month') $query->where('created_at', '>=', now()->subMonth());
        if (($filters['date'] ?? null) === 'year') $query->where('created_at', '>=', now()->subYear());

        if (($filters['sort'] ?? 'relevance') === 'views') return $query->orderByDesc('views')->latest();
        if (($filters['sort'] ?? 'relevance') === 'newest' || $term === '') return $query->latest();

        return $query
            ->orderByRaw('CASE WHEN title = ? THEN 3 WHEN title LIKE ? THEN 2 WHEN description LIKE ? THEN 1 ELSE 0 END DESC', [$term, '%'.$term.'%', '%'.$term.'%'])
            ->latest();
    }

    private function recordSearch(string $query, Request $request): void
    {
        SearchHistory::create([
            'user_id' => $request->user()?->id,
            'query' => $query,
            'normalized_query' => mb_strtolower($query),
            'searched_at' => now(),
        ]);
    }
}
