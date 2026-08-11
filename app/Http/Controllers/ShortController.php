<?php

namespace App\Http\Controllers;

use App\Models\Subscription;
use App\Models\CommentReaction;
use App\Models\Video;
use App\Models\VideoLike;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Collection;
use Illuminate\View\View;

class ShortController extends Controller
{
    public function index(): View|JsonResponse
    {
        $shorts = Video::query()
            ->published()
            ->shorts()
            ->with(['user', 'category'])
            ->latest()
            ->paginate(12);

        if (request()->expectsJson()) {
            return response()->json([
                'data' => $shorts->getCollection()->map(fn (Video $short) => [
                    'url' => route('shorts.show', $short),
                    'title' => $short->title,
                    'thumbnail_url' => $short->thumbnail_url,
                    'channel' => $short->display_channel_name,
                    'views' => number_format($short->views),
                ])->values(),
                'next_page_url' => $shorts->nextPageUrl(),
            ]);
        }

        return view('shorts.index', compact('shorts'));
    }

    public function show(Video $video): View
    {
        $viewer = auth()->user();

        abort_unless($video->is_short && $video->isVisibleTo($viewer), 404);
        abort_unless($video->isPremiumAccessibleTo($viewer), 403);

        $nextShorts = Video::query()
            ->published()
            ->shorts()
            ->where('id', '!=', $video->id)
            ->with(['user', 'category'])
            ->orderByDesc('views')
            ->latest()
            ->take(8)
            ->get();

        $feed = collect([$video])->concat($nextShorts);
        $this->loadFeedRelations($feed);

        $shortIds = $feed->pluck('id');
        $channelIds = $feed->pluck('user_id')->unique()->values();

        $likedIds = collect();
        $dislikedIds = collect();
        $savedIds = collect();
        $subscribedChannelIds = collect();

        if ($viewer) {
            $likedIds = VideoLike::query()
                ->where('user_id', $viewer->id)
                ->where('reaction', 'like')
                ->whereIn('video_id', $shortIds)
                ->pluck('video_id');
            $dislikedIds = VideoLike::query()
                ->where('user_id', $viewer->id)
                ->where('reaction', 'dislike')
                ->whereIn('video_id', $shortIds)
                ->pluck('video_id');
            $savedIds = $viewer->watchLaterVideos()
                ->whereIn('videos.id', $shortIds)
                ->pluck('videos.id');
            $subscribedChannelIds = $viewer->subscriptions()
                ->whereIn('channel_id', $channelIds)
                ->pluck('channel_id');
        }

        $subscriberCounts = Subscription::query()
            ->selectRaw('channel_id, COUNT(*) as subscribers_count')
            ->whereIn('channel_id', $channelIds)
            ->groupBy('channel_id')
            ->pluck('subscribers_count', 'channel_id');

        $commentReactions = collect();

        if ($viewer) {
            $commentIds = $feed
                ->flatMap(fn (Video $short) => $short->comments->pluck('id'))
                ->filter()
                ->unique()
                ->values();

            $commentReactions = CommentReaction::query()
                ->where('user_id', $viewer->id)
                ->whereIn('comment_id', $commentIds)
                ->pluck('reaction', 'comment_id');
        }

        return view('shorts.show', compact(
            'video',
            'feed',
            'likedIds',
            'dislikedIds',
            'savedIds',
            'subscribedChannelIds',
            'subscriberCounts',
            'commentReactions',
        ));
    }

    private function loadFeedRelations(Collection $feed): void
    {
        $feed->each(function (Video $short): void {
            $short->loadCount(['likes', 'dislikes', 'comments']);
            $short->load([
                'user',
                'comments' => fn ($query) => $query
                    ->whereNull('parent_id')
                    ->with('user')
                    ->withCount([
                        'likes',
                        'dislikes',
                    ])
                    ->orderByDesc('is_pinned')
                    ->latest()
                    ->take(30),
            ]);
        });
    }
}
