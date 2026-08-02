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
use App\Models\WatchHistory;

#[Fillable([
    'name',
    'email',
    'password',

    'channel_name',
    'channel_description',
    'avatar',
    'banner',
])]
#[Hidden(['password', 'remember_token'])]
class User extends Authenticatable
{
    /** @use HasFactory<UserFactory> */
    use HasFactory, Notifiable;

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
        ];
    }
}
