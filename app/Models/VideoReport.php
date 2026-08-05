<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class VideoReport extends Model
{
    public const REASONS = [
        'spam' => 'Spam veya yanıltıcı içerik',
        'harassment' => 'Taciz veya nefret söylemi',
        'copyright' => 'Telif hakkı ihlali',
        'sexual' => 'Uygunsuz cinsel içerik',
        'violence' => 'Şiddet veya tehlikeli içerik',
        'other' => 'Diğer',
    ];

    public const STATUSES = [
        'open' => 'Açık',
        'reviewing' => 'İnceleniyor',
        'resolved' => 'Çözüldü',
        'dismissed' => 'Reddedildi',
    ];

    protected $fillable = [
        'video_id',
        'reporter_id',
        'reason',
        'details',
        'status',
        'reviewed_by',
        'reviewed_at',
    ];

    protected function casts(): array
    {
        return [
            'reviewed_at' => 'datetime',
        ];
    }

    public function video(): BelongsTo
    {
        return $this->belongsTo(Video::class);
    }

    public function reporter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reporter_id');
    }

    public function reviewer(): BelongsTo
    {
        return $this->belongsTo(User::class, 'reviewed_by');
    }
}
