<?php

namespace App\Http\Controllers\Api\V1\Admin;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\CategoryResource;
use App\Http\Resources\Api\VideoResource;
use App\Models\Category;
use App\Models\Comment;
use App\Models\User;
use App\Models\Video;
use App\Services\AdminActivityLogger;
use App\Services\AnalyticsService;
use App\Services\ContentCache;
use App\Services\VideoDeletionService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;

class ContentController extends Controller
{
    public function __construct(private readonly VideoDeletionService $deletion, private readonly AnalyticsService $analytics, private readonly AdminActivityLogger $activity) {}

    public function videos(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'type' => ['nullable', Rule::in(['video', 'short'])], 'status' => ['nullable', Rule::in(['public', 'private', 'unlisted', 'draft'])], 'limit' => ['nullable', 'integer', 'min:1', 'max:50']]);
        return VideoResource::collection(Video::query()->with(['user', 'category'])->withCount(['likes', 'comments'])
            ->when(filled($filters['q'] ?? null), fn ($q) => $q->where('title', 'like', '%'.$filters['q'].'%'))
            ->when(($filters['type'] ?? null) === 'video', fn ($q) => $q->where('is_short', false))
            ->when(($filters['type'] ?? null) === 'short', fn ($q) => $q->where('is_short', true))
            ->when($filters['status'] ?? null, fn ($q, $status) => $q->where('status', $status))
            ->latest()->paginate($filters['limit'] ?? 20)->withQueryString());
    }

    public function updateVideo(Request $request, Video $video): JsonResponse
    {
        $data = $request->validate(['status' => ['sometimes', Rule::in(['public', 'private', 'unlisted', 'draft'])], 'is_featured' => ['sometimes', 'boolean'], 'age_restriction' => ['sometimes', Rule::in([0, 13, 16, 18])]]);
        $video->update($data); ContentCache::flush();
        $this->activity->record($request->user('sanctum'), 'video.moderated', 'Video moderasyon ayarları güncellendi.', $video, $data);
        return (new VideoResource($video->fresh()->load(['user', 'category'])->loadCount(['likes', 'comments'])))->response();
    }

    public function destroyVideo(Request $request, Video $video): JsonResponse
    {
        $id = $video->id; $this->deletion->delete($video); ContentCache::flush();
        $this->activity->record($request->user('sanctum'), 'video.deleted', 'Video yönetici tarafından silindi.', null, ['video_id' => $id]);
        return response()->json(null, 204);
    }

    public function comments(Request $request): JsonResponse
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'limit' => ['nullable', 'integer', 'min:1', 'max:50']]);
        $items = Comment::query()->with(['user', 'video.user'])
            ->when(filled($filters['q'] ?? null), fn ($q) => $q->where('comment', 'like', '%'.$filters['q'].'%'))
            ->latest()->paginate($filters['limit'] ?? 20)->withQueryString();
        return response()->json(['data' => $items->getCollection()->map(fn (Comment $comment) => $this->commentData($comment)), 'meta' => $this->meta($items)]);
    }

    public function destroyComment(Request $request, Comment $comment): JsonResponse
    {
        $video = $comment->video; $id = $comment->id; $comment->delete(); $this->analytics->syncComments($video);
        $this->activity->record($request->user('sanctum'), 'comment.deleted', 'Yorum yönetici tarafından silindi.', null, ['comment_id' => $id]);
        return response()->json(null, 204);
    }

    public function channels(Request $request): AnonymousResourceCollection
    {
        $filters = $request->validate(['q' => ['nullable', 'string', 'max:100'], 'limit' => ['nullable', 'integer', 'min:1', 'max:50']]);
        return \App\Http\Resources\Api\ManagedUserResource::collection(User::query()
            ->when(filled($filters['q'] ?? null), fn ($q) => $q->where(fn ($users) => $users->where('name', 'like', '%'.$filters['q'].'%')->orWhere('channel_name', 'like', '%'.$filters['q'].'%')))
            ->withCount(['subscribers', 'videos'])->latest()->paginate($filters['limit'] ?? 20)->withQueryString());
    }

    public function categories(Request $request): AnonymousResourceCollection
    {
        $limit = $request->validate(['limit' => ['nullable', 'integer', 'min:1', 'max:50']])['limit'] ?? 20;
        return CategoryResource::collection(Category::query()->withCount('videos')->orderBy('name')->paginate($limit));
    }

    public function storeCategory(Request $request): JsonResponse
    {
        $data = $request->validate(['name' => ['required', 'string', 'max:100'], 'slug' => ['nullable', 'string', 'max:120', 'alpha_dash', 'unique:categories,slug']]);
        $slug = $data['slug'] ?? Str::slug($data['name']);
        if (Category::query()->where('slug', $slug)->exists()) {
            abort(422, 'Bu kategori zaten mevcut.');
        }

        $category = Category::create(['name' => $data['name'], 'slug' => $slug]); ContentCache::flush();
        return (new CategoryResource($category))->response()->setStatusCode(201);
    }

    public function updateCategory(Request $request, Category $category): JsonResponse
    {
        $data = $request->validate(['name' => ['sometimes', 'string', 'max:100'], 'slug' => ['sometimes', 'string', 'max:120', 'alpha_dash', Rule::unique('categories', 'slug')->ignore($category->id)]]);
        $category->update($data); ContentCache::flush(); return (new CategoryResource($category->fresh()))->response();
    }

    public function destroyCategory(Category $category): JsonResponse
    {
        abort_if($category->videos()->exists(), 422, 'Video içeren kategori silinemez.'); $category->delete(); ContentCache::flush(); return response()->json(null, 204);
    }

    public function settings(): JsonResponse
    {
        $disk = Storage::disk('public');
        $storageBytes = collect($disk->allFiles())->sum(fn (string $file): int => $disk->size($file));
        return response()->json(['data' => ['app_env' => config('app.env'), 'app_version' => config('app.version', '1.0'), 'storage_bytes' => $storageBytes, 'queue_connection' => config('queue.default'), 'cache_store' => config('cache.default')]]);
    }

    private function commentData(Comment $comment): array { return ['id' => $comment->id, 'comment' => $comment->comment, 'user' => ['id' => $comment->user?->id, 'name' => $comment->user?->channel_name ?: $comment->user?->name], 'video' => ['id' => $comment->video?->id, 'title' => $comment->video?->title], 'created_at' => $comment->created_at?->toISOString()]; }
    private function meta($page): array { return ['current_page' => $page->currentPage(), 'last_page' => $page->lastPage(), 'per_page' => $page->perPage(), 'total' => $page->total()]; }
}
