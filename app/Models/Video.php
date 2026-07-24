<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Video extends Model
{
    protected $fillable = [
        'title',
        'description',
        'thumbnail',
        'preview',
        'video_path',
        'channel_name',
        'views',
        'duration',
        'user_id',
    ];

    protected $attributes = [
        'views' => 0,
    ];

    /**
     * Videonun sahibi
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Videoya ait yorumlar
     */
    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class)->latest();
    }

    /**
     * Videoya ait beğeniler
     */
    public function likes(): HasMany
    {
        return $this->hasMany(VideoLike::class);
    }

    /**
     * Thumbnail URL
     */
    public function getThumbnailUrlAttribute(): string
    {
        return asset('storage/' . $this->thumbnail);
    }

    /**
     * Preview URL
     */
    public function getPreviewUrlAttribute(): ?string
    {
        if (!$this->preview) {
            return null;
        }

        return asset('storage/' . $this->preview);
    }

    /**
     * Video URL
     */
    public function getVideoUrlAttribute(): string
    {
        return asset('storage/' . $this->video_path);
    }
}