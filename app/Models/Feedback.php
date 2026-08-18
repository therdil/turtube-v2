<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Feedback extends Model
{
    public const TYPES = ['suggestion', 'complaint'];
    public const STATUSES = ['pending', 'reviewing', 'resolved', 'rejected'];

    protected $fillable = ['user_id', 'type', 'subject', 'message', 'status', 'admin_note', 'reviewed_by', 'reviewed_at'];
    protected function casts(): array { return ['reviewed_at' => 'datetime']; }
    public function user(): BelongsTo { return $this->belongsTo(User::class); }
    public function reviewer(): BelongsTo { return $this->belongsTo(User::class, 'reviewed_by'); }
}
