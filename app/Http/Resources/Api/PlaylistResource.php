<?php

namespace App\Http\Resources\Api;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/** @mixin \App\Models\Playlist */
class PlaylistResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name' => $this->name,
            'description' => $this->description,
            'is_public' => (bool) $this->is_public,
            'videos_count' => (int) ($this->videos_count ?? 0),
            'created_at' => $this->created_at?->toISOString(),
            'videos' => VideoResource::collection($this->whenLoaded('videos')),
        ];
    }
}
