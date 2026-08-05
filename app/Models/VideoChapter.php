<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoChapter extends Model
{
    protected $fillable = ['video_id', 'title', 'start_seconds'];

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function getFormattedStartAttribute(): string
    {
        return $this->start_seconds >= 3600
            ? gmdate('H:i:s', $this->start_seconds)
            : gmdate('i:s', $this->start_seconds);
    }
}
