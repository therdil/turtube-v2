<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Models\WatchHistory;
use App\Models\VideoProgress;
use App\Services\MediaUrl;

class Video extends Model
{
    protected $fillable = [
        'title',
        'description',
        'thumbnail',
        'preview',
        'video_path',
        'video_qualities',
        'processing_status',
        'processing_error',
        'channel_name',
        'views',
        'duration',
        'user_id',
        'category_id',
        'status',
        'license',
        'tags',
        'is_short',
        'is_premium',
        'is_featured',
        'age_restriction',
        'copyright_status',
        'copyright_note',
    ];

    protected $attributes = [
        'views' => 0,
        'status' => 'public',
    ];

    /**
     * Herkese açık video sorgusu.
     */
    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'public');
    }

    public function scopeShorts(Builder $query): Builder
    {
        return $query->where('is_short', true);
    }

    /**
     * Videonun belirtilen kullanıcı tarafından görüntülenip görüntülenemeyeceği.
     */
    public function isVisibleTo(?User $user): bool
    {
        return in_array($this->status, ['public', 'unlisted'], true)
            || $this->user_id === $user?->id;
    }

    public function isPremiumAccessibleTo(?User $user): bool
    {
        return ! $this->is_premium
            || $this->user_id === $user?->id
            || $user?->hasPremiumAccess();
    }

    protected function casts(): array
    {
        return [
            'is_short' => 'boolean',
            'is_premium' => 'boolean',
            'is_featured' => 'boolean',
            'age_restriction' => 'integer',
            'video_qualities' => 'array',
            'tags' => 'array',
        ];
    }

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
     * Videoya gönderilen moderasyon raporları.
     */
    public function reports(): HasMany
    {
        return $this->hasMany(VideoReport::class);
    }

    public function chapters(): HasMany
    {
        return $this->hasMany(VideoChapter::class)->orderBy('start_seconds');
    }

    public function captions(): HasMany
    {
        return $this->hasMany(VideoCaption::class);
    }

    public function favorites(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'video_favorites')->withTimestamps();
    }

    public function ratings(): HasMany
    {
        return $this->hasMany(VideoRating::class);
    }

    public function viewEvents(): HasMany
    {
        return $this->hasMany(VideoViewEvent::class);
    }

    /**
     * Thumbnail URL
     */
    public function getThumbnailUrlAttribute(): string
    {
        return MediaUrl::for($this->thumbnail) ?: '';
    }

    /**
     * Preview URL
     */
    public function getPreviewUrlAttribute(): ?string
    {
        if (!$this->preview) {
            return null;
        }

        return MediaUrl::for($this->preview);
    }

    /**
     * Video URL
     */
    public function getVideoUrlAttribute(): string
    {
        return MediaUrl::for($this->video_path) ?: '';
    }

    /**
     * Playback sources are ordered from the original upload to generated variants.
     * A source is only exposed when its file was created successfully.
     */
    public function getPlaybackSourcesAttribute(): array
    {
        $sources = [];

        foreach ($this->video_qualities ?? [] as $quality) {
            if (! empty($quality['path']) && ! empty($quality['label'])) {
                $sources[] = [
                    'label' => $quality['label'],
                    'path' => $quality['path'],
                    'url' => MediaUrl::for($quality['path']),
                ];
            }
        }

        if (empty($sources) && $this->video_path) {
            $sources[] = [
                'label' => 'Orijinal',
                'path' => $this->video_path,
                'url' => $this->video_url,
            ];
        }

        return $sources;
    }

    public function getFormattedDurationAttribute(): ?string
    {
        $seconds = (int) $this->duration;

        if ($seconds <= 0) {
            return null;
        }

        return $seconds >= 3600
            ? gmdate('H:i:s', $seconds)
            : gmdate('i:s', $seconds);
    }

    /**
     * Kanal adını kullanıcıdan al
     */
    public function getDisplayChannelNameAttribute(): string
    {
        return $this->user?->channel_name ?: $this->user?->name ?: $this->channel_name;
    }
}
