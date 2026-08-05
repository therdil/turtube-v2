<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class LiveStream extends Model
{
    use HasFactory;

    protected $fillable = [
        'title',
        'description',
        'thumbnail',
        'stream_key',
        'stream_url',
        'status',
        'scheduled_at',
        'started_at',
        'ended_at',
        'viewer_count',
    ];

    protected function casts(): array
    {
        return [
            'scheduled_at' => 'datetime',
            'started_at' => 'datetime',
            'ended_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
