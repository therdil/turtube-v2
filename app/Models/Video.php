<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

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

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function getThumbnailUrlAttribute(): string
    {
        return asset('storage/' . $this->thumbnail);
    }

    public function getPreviewUrlAttribute(): ?string
    {
        if (!$this->preview) {
            return null;
        }

        return asset('storage/' . $this->preview);
    }

    public function getVideoUrlAttribute(): string
    {
        return asset('storage/' . $this->video_path);
    }
}