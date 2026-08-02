<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoProgress extends Model
{
    protected $table = 'video_progress';

    protected $fillable = [
        'user_id',
        'video_id',
        'seconds',
        'percentage',
    ];

    protected $casts = [
        'percentage' => 'float',
    ];

    /**
     * Kullanıcı
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Video
     */
    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }
}