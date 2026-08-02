<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoAnalytics extends Model
{
    protected $table = 'video_analytics';

    protected $fillable = [
        'video_id',
        'date',
        'views',
        'watch_time',
        'likes',
        'comments',
    ];

    protected $casts = [
        'date' => 'date',
    ];

    /**
     * Analitik kaydının ait olduğu video
     */
    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }
}