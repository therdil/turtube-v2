<?php

namespace App\Http\Resources\Api;

use App\Services\MediaUrl;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Video */
class VideoResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        $playbackSources = collect($this->video_qualities ?? [])
            ->filter(fn (array $quality) => filled($quality['path'] ?? null) && filled($quality['label'] ?? null))
            ->map(fn (array $quality) => [
                'label' => $quality['label'],
                'url' => MediaUrl::absoluteFor($quality['path']),
            ])
            ->values();

        if ($playbackSources->isEmpty() && filled($this->video_path)) {
            $playbackSources->push([
                'label' => 'Orijinal',
                'url' => MediaUrl::absoluteFor($this->video_path),
            ]);
        }

        return [
            'id' => $this->id,
            'title' => $this->title,
            'description' => $this->description,
            'thumbnail_url' => MediaUrl::absoluteFor($this->thumbnail),
            'preview_url' => MediaUrl::absoluteFor($this->preview),
            'video_url' => MediaUrl::absoluteFor($this->video_path),
            'playback_sources' => $playbackSources->all(),
            'duration' => (int) $this->duration,
            'views' => (int) $this->views,
            'likes_count' => (int) ($this->likes_count ?? 0),
            'comments_count' => (int) ($this->comments_count ?? 0),
            'published_at' => $this->created_at?->toISOString(),
            'is_short' => (bool) $this->is_short,
            'is_premium' => (bool) $this->is_premium,
            'processing_status' => $this->processing_status,
            'category' => CategoryResource::make($this->whenLoaded('category')),
            'channel' => UserResource::make($this->whenLoaded('user')),
        ];
    }
}
