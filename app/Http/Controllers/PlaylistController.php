<?php

namespace App\Http\Controllers;

use App\Models\Playlist;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class PlaylistController extends Controller
{
    /**
     * Kullanıcının oynatma listeleri
     */
    public function index(): View
    {
        $playlists = auth()->user()
            ->playlists()
            ->withCount('videos')
            ->latest()
            ->get();

        return view('playlists.index', compact('playlists'));
    }

    /**
     * Yeni playlist oluşturma formu
     */
    public function create(): View
    {
        return view('playlists.create');
    }

    /**
     * Playlist oluştur
     */
    public function store(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'description' => ['nullable', 'string', 'max:1000'],
            'is_public' => ['nullable', 'boolean'],
        ]);

        auth()->user()->playlists()->create([
            'name' => $validated['name'],
            'description' => $validated['description'] ?? null,
            'is_public' => $request->boolean('is_public'),
        ]);

        return redirect()
            ->route('playlists.index')
            ->with('success', 'Playlist başarıyla oluşturuldu.');
    }

    /**
     * Playlist detay
     */
    public function show(Playlist $playlist): View
    {
        $this->authorize('view', $playlist);

        $isOwner = $playlist->user_id === auth()->id();

        $playlist->load([
            'user',
            'videos' => function ($query) use ($isOwner) {
                $query
                    ->when(! $isOwner, fn ($videos) => $videos->published())
                    ->with(['user', 'category']);
            },
        ]);

        return view('playlists.show', compact('playlist'));
    }

    /**
     * Videoyu playlist'e ekle / çıkar
     */
    public function toggle(Request $request, Playlist $playlist): JsonResponse|RedirectResponse
    {
        $this->authorize('update', $playlist);

        $validated = $request->validate([
            'video_id' => ['required', 'exists:videos,id'],
        ]);

        $videoId = $validated['video_id'];

        $video = \App\Models\Video::findOrFail($videoId);

        abort_unless(
            $video->isVisibleTo(auth()->user()) && $video->isPremiumAccessibleTo(auth()->user()),
            404
        );

        $exists = $playlist->videos()
            ->where('video_id', $videoId)
            ->exists();

        if ($exists) {

            $playlist->videos()->detach($videoId);

            if (! $request->expectsJson()) {
                return back()->with('success', 'Video playlistten kaldırıldı.');
            }

            return response()->json([
                'added' => false,
                'message' => 'Video playlistten kaldırıldı.',
            ]);
        }

        $playlist->videos()->attach($videoId);

        if (! $request->expectsJson()) {
            return back()->with('success', 'Video playliste eklendi.');
        }

        return response()->json([
            'added' => true,
            'message' => 'Video playliste eklendi.',
        ]);
    }

    /**
     * Playlist sil
     */
    public function destroy(Playlist $playlist): RedirectResponse
    {
        $this->authorize('delete', $playlist);

        $playlist->delete();

        return redirect()
            ->route('playlists.index')
            ->with('success', 'Playlist silindi.');
    }
}
