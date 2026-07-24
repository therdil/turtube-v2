<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Comment extends Model
{
    protected $fillable = [
        'video_id',
        'user_id',
        'comment',
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
}