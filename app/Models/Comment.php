<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Comment extends Model
{
    protected $fillable = [
        'video_id',
        'user_id',
        'parent_id',
        'comment',
        'is_pinned',
    ];

    /**
     * Yorumu yapan kullanıcı
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Yorumun ait olduğu video
     */
    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function parent(): BelongsTo
    {
        return $this->belongsTo(self::class, 'parent_id');
    }

    public function replies(): HasMany
    {
        return $this->hasMany(self::class, 'parent_id')->oldest();
    }

    public function reactions(): HasMany
    {
        return $this->hasMany(CommentReaction::class);
    }

    public function likes(): HasMany
    {
        return $this->reactions()->where('reaction', 'like');
    }

    public function dislikes(): HasMany
    {
        return $this->reactions()->where('reaction', 'dislike');
    }

    protected function casts(): array
    {
        return ['is_pinned' => 'boolean'];
    }
}
