<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\User;
use App\Services\ContentCache;
use Illuminate\Http\Request;
use Illuminate\View\View;

class ChannelController extends Controller
{
    public function show(Request $request, User $user): View
    {
        $validated = $request->validate([
            'tab' => ['nullable', 'in:videos,shorts,playlists,about'],
            'q' => ['nullable', 'string', 'max:100'],
        ]);

        $tab = $validated['tab'] ?? 'videos';
        $query = trim($validated['q'] ?? '');
        $isOwner = $request->user()?->is($user) ?? false;
        $page = max(1, (int) $request->input('page', 1));

        $data = ! $request->user()
            ? ContentCache::remember('channel', implode(':', [$user->id, $tab, sha1(mb_strtolower($query)), $page]), 180, fn () => $this->data($user, $tab, $query, false))
            : $this->data($user, $tab, $query, $isOwner, $request->user()?->id);

        return view('channels.show', $data);
    }

    private function data(User $user, string $tab, string $query, bool $isOwner, ?int $viewerId = null): array
    {
        $visibleVideos = $user->videos()->when(! $isOwner, fn ($videos) => $videos->published());
        $videos = (clone $visibleVideos)
            ->when($tab === 'videos', fn ($videos) => $videos->where('is_short', false))
            ->when($tab === 'shorts', fn ($videos) => $videos->where('is_short', true))
            ->when($query !== '', function ($videos) use ($query) {
                $videos->where(fn ($search) => $search->where('title', 'like', '%'.$query.'%')->orWhere('description', 'like', '%'.$query.'%'));
            })
            ->with(['user', 'category'])
            ->latest()
            ->paginate(16)
            ->withQueryString();

        $playlists = $user->playlists()
            ->when(! $isOwner, fn ($items) => $items->where('is_public', true))
            ->withCount('videos')
            ->latest()
            ->get();

        return [
            'channel' => $user,
            'tab' => $tab,
            'videos' => $videos,
            'playlists' => $playlists,
            'subscribersCount' => Subscription::where('channel_id', $user->id)->count(),
            'totalViews' => (clone $visibleVideos)->sum('views'),
            'totalVideos' => (clone $visibleVideos)->where('is_short', false)->count(),
            'shortsCount' => (clone $visibleVideos)->where('is_short', true)->count(),
            'isSubscribed' => $viewerId ? Subscription::where('subscriber_id', $viewerId)->where('channel_id', $user->id)->exists() : false,
        ];
    }
}
