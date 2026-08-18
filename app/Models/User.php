<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Database\Factories\UserFactory;
use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Attributes\Hidden;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use App\Models\WatchHistory;

#[Fillable([
    'name',
    'email',
    'password',

    'channel_name',
    'channel_description',
    'avatar',
    'banner',
    'social_links',
    'channel_tags',
    'seo_keywords',
    'channel_language',
    'default_video_status',
    'default_video_description',
    'default_video_license',
    'is_admin',
    'is_moderator',
    'is_verified',
    'premium_until',
    'banned_at',
    'ban_reason',
    'theme_preference',
    'notification_likes_enabled',
    'notification_comments_enabled',
    'notification_subscribers_enabled',
    'notification_system_enabled',
    'channel_visibility',
    'subscription_visibility',
    'playlist_visibility',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /** Keep newly-created in-memory users aligned with database preference defaults. */
    protected $attributes = [
        'notification_likes_enabled' => true,
        'notification_comments_enabled' => true,
        'notification_subscribers_enabled' => true,
        'notification_system_enabled' => true,
        'channel_visibility' => 'public',
        'subscription_visibility' => true,
        'playlist_visibility' => 'public',
    ];

    /**
     * Route Model Binding için kullanıcı adı kullan.
     */
    public function getRouteKeyName(): string
    {
        return 'name';
    }

    /**
     * Kullanıcının yüklediği videolar
     */
    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }

    /**
     * Kullanıcının yaptığı video beğenileri
     */
    public function videoLikes(): HasMany
    {
        return $this->hasMany(VideoLike::class);
    }

    /**
     * Kullanıcının beğendiği videolar
     */
    public function likedVideos(): BelongsToMany
    {
        return $this->belongsToMany(Video::class, 'video_likes')
            ->wherePivot('reaction', '=', 'like')
            ->withTimestamps();
    }

    /**
     * Kullanıcının abone olduğu kanallar
     */
    public function subscriptions(): HasMany
    {
        return $this->hasMany(Subscription::class, 'subscriber_id');
    }

    /**
     * Kullanıcının abone olduğu kanallar
     */
    public function subscribedChannels(): BelongsToMany
    {
        return $this->belongsToMany(
            self::class,
            'subscriptions',
            'subscriber_id',
            'channel_id'
        )->withTimestamps();
    }

    /**
     * Kullanıcının Daha Sonra İzle listesi
     */
    public function watchLaterVideos(): BelongsToMany
    {
        return $this->belongsToMany(
        Video::class,
        'watch_laters'
        )->withTimestamps();
    }

    public function favoriteVideos(): BelongsToMany
    {
        return $this->belongsToMany(Video::class, 'video_favorites')->withTimestamps();
    }

    public function videoRatings(): HasMany
    {
        return $this->hasMany(VideoRating::class);
    }

    public function comments(): HasMany
    {
        return $this->hasMany(Comment::class);
    }

    public function videoReports(): HasMany
    {
        return $this->hasMany(VideoReport::class, 'reporter_id');
    }

    public function feedback(): HasMany
    {
        return $this->hasMany(Feedback::class);
    }

    /**
     * Kullanıcının oynatma listeleri
     */
    public function playlists(): HasMany
    {
        return $this->hasMany(Playlist::class);
    }

    /**
     * Kullanıcının aboneleri
     */
    public function subscribers(): HasMany
    {
        return $this->hasMany(Subscription::class, 'channel_id');
    }

    /**
     * Kullanıcının izleme geçmişi
     */
    public function watchHistory(): HasMany
    {
        return $this->hasMany(WatchHistory::class)
            ->latest('watched_at');
    }

    public function liveStreams(): HasMany
    {
        return $this->hasMany(LiveStream::class);
    }

    public function hasPremiumAccess(): bool
    {
        return $this->premium_until?->isFuture() ?? false;
    }

    /**
     * Returns the server-authoritative platform role for authenticated clients.
     */
    public function platformRole(): string
    {
        if ($this->is_admin) {
            return 'admin';
        }

        return $this->is_moderator ? 'moderator' : 'user';
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_admin' => 'boolean',
            'is_moderator' => 'boolean',
            'is_verified' => 'boolean',
            'premium_until' => 'datetime',
            'banned_at' => 'datetime',
            'social_links' => 'array',
            'channel_tags' => 'array',
            'seo_keywords' => 'array',
            'notification_likes_enabled' => 'boolean',
            'notification_comments_enabled' => 'boolean',
            'notification_subscribers_enabled' => 'boolean',
            'notification_system_enabled' => 'boolean',
            'subscription_visibility' => 'boolean',
        ];
    }
}
