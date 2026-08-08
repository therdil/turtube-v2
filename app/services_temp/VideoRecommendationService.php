<?php

namespace App\Services;

use App\Models\User;
use App\Models\Video;
use Illuminate\Support\Collection;

class VideoRecommendationService
{
    /**
     * İzleyicinin geçmişi, mevcut video bağlamı ve popülerlik sinyallerini
     * birleştirerek yayınlanabilir video önerileri üretir.
     *
     * @return Collection<int, Video>
     */
    public function forVideo(Video $video, ?User $viewer, int $limit = 8): Collection
    {
        $recentlyWatched = $viewer
            ? $viewer->watchHistory()
                ->with('video:id,user_id,category_id,tags')
                ->take(20)
                ->get()
                ->pluck('video')
                ->filter()
            : collect();

        $watchedVideoIds = $recentlyWatched->pluck('id')->all();
        $watchedCategoryIds = $recentlyWatched->pluck('category_id')->filter()->countBy();
        $watchedChannelIds = $recentlyWatched->pluck('user_id')->filter()->countBy();
        $watchedTags = $recentlyWatched
            ->flatMap(fn (Video $watchedVideo) => $watchedVideo->tags ?? [])
            ->filter()
            ->countBy();
        $currentTags = collect($video->tags ?? [])->filter();

        $candidates = Video::query()
            ->published()
            ->where('id', '!=', $video->id)
            ->when($viewer && $watchedVideoIds, fn ($query) => $query->whereNotIn('id', $watchedVideoIds))
            ->when(! $viewer?->hasPremiumAccess(), function ($query) use ($viewer) {
                $query->where(function ($premiumQuery) use ($viewer) {
                    $premiumQuery->where('is_premium', false)
                        ->orWhere('user_id', $viewer?->id);
                });
            })
            ->with(['user', 'category'])
            ->orderByDesc('is_featured')
            ->orderByDesc('views')
            ->latest()
            ->take(max(40, $limit * 6))
            ->get();

        return $candidates
            ->map(function (Video $candidate) use ($video, $currentTags, $watchedCategoryIds, $watchedChannelIds, $watchedTags) {
                $score = min(24, log10((int) $candidate->views + 1) * 6);

                if ($candidate->category_id && $candidate->category_id === $video->category_id) {
                    $score += 28;
                }

                if ($candidate->user_id === $video->user_id) {
                    $score += 18;
                }

                $candidateTags = collect($candidate->tags ?? [])->filter();
                $score += $candidateTags->intersect($currentTags)->count() * 8;
                $score += min(18, (int) ($watchedCategoryIds[$candidate->category_id] ?? 0) * 5);
                $score += min(15, (int) ($watchedChannelIds[$candidate->user_id] ?? 0) * 4);
                $score += min(20, $candidateTags->sum(fn ($tag) => (int) ($watchedTags[$tag] ?? 0) * 3));
                $score += $candidate->is_featured ? 6 : 0;
                $score += max(0, 6 - min(6, now()->diffInDays($candidate->created_at) / 7));

                $candidate->recommendation_score = round($score, 2);

                return $candidate;
            })
            ->sortByDesc('recommendation_score')
            ->take($limit)
            ->values();
    }
}
