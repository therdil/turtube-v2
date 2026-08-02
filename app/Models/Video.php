<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\WatchHistory;
use App\Models\VideoProgress;

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
        'category_id',
        'status',
    ];

    protected $attributes = [
        'views' => 0,
        'status' => 'public',
    ];

    /**
     * Videonun sahibi
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Videonun bulunduğu oynatma listeleri
     */
    public function playlists(): BelongsToMany
    {
        return $this->belongsToMany(Playlist::class)
            ->withTimestamps();
    }

    /**
     * Videonun kategorisi
     */
    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class);
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
     * Videoyu izleyen kullanıcıların geçmiş kayıtları
     */
    public function watchHistories(): HasMany
    {
        return $this->hasMany(WatchHistory::class);
    }

    /**
     * Kullanıcının bu videodaki izleme ilerlemesi
     */
    public function progress(): HasMany
    {
        return $this->hasMany(VideoProgress::class);
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

    /**
     * Kanal adını kullanıcıdan al
     */
        public function getDisplayChannelNameAttribute(): string
    {
        return $this->user->channel_name ?: $this->user->name;
    }
}
