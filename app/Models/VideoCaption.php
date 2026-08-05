<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use App\Services\MediaUrl;

class VideoCaption extends Model
{
    protected $fillable = ['video_id', 'language', 'label', 'path', 'is_default'];

    protected function casts(): array
    {
        return ['is_default' => 'boolean'];
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function getUrlAttribute(): string
    {
        return MediaUrl::for($this->path) ?: '';
    }
}
