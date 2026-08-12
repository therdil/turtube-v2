<?php

namespace App\Http\Controllers\Api\V1;

use App\Http\Controllers\Controller;
use App\Http\Resources\Api\PlaylistResource;
use App\Models\Playlist;
use App\Models\Video;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
use Illuminate\Validation\Rule;

class PlaylistController extends Controller
{
    public function index(Request $request): AnonymousResourceCollection
    {
        return PlaylistResource::collection($request->user('sanctum')->playlists()
            ->withCount('videos')->latest()->paginate(20));
    }

    public function store(Request $request): JsonResponse
    {
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_public' => ['nullable', 'boolean'],
        ]);
        $playlist = $request->user('sanctum')->playlists()->create([
            ...$data,
            'is_public' => $request->boolean('is_public'),
        ]);

        return (new PlaylistResource($playlist->loadCount('videos')))
            ->additional(['message' => 'Oynatma listesi oluşturuldu.'])
            ->response()->setStatusCode(201);
    }

    public function show(Request $request, Playlist $playlist): PlaylistResource
    {
        $this->authorize('view', $playlist);
        $owner = $playlist->user_id === $request->user('sanctum')->id;
        $playlist->loadCount('videos')->load(['videos' => fn ($query) => $query
            ->when(! $owner, fn ($videos) => $videos->published())
            ->with(['user', 'category'])->withCount(['likes', 'comments'])]);

        return new PlaylistResource($playlist);
    }

    public function update(Request $request, Playlist $playlist): PlaylistResource
    {
        $this->authorize('update', $playlist);
        $data = $request->validate([
            'name' => ['sometimes', 'required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_public' => ['nullable', 'boolean'],
        ]);
        $playlist->update($data);

        return new PlaylistResource($playlist->loadCount('videos'));
    }

    public function destroy(Playlist $playlist): JsonResponse
    {
        $this->authorize('delete', $playlist);
        $playlist->delete();

        return response()->json(['message' => 'Oynatma listesi silindi.']);
    }

    public function addVideo(Request $request, Playlist $playlist): PlaylistResource
    {
        $this->authorize('update', $playlist);
        $data = $request->validate(['video_id' => ['required', 'integer', Rule::exists('videos', 'id')]]);
        $video = Video::findOrFail($data['video_id']);
        abort_unless($video->isVisibleTo($request->user('sanctum')) && $video->isPremiumAccessibleTo($request->user('sanctum')), 404);
        $playlist->videos()->syncWithoutDetaching([$video->id]);

        return new PlaylistResource($playlist->loadCount('videos'));
    }

    public function removeVideo(Playlist $playlist, Video $video): JsonResponse
    {
        $this->authorize('update', $playlist);
        $playlist->videos()->detach($video->id);

        return response()->json(['message' => 'Video oynatma listesinden kaldırıldı.']);
    }
}
