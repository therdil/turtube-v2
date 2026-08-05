<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class SearchHistory extends Model
{
    public $timestamps = false;

    protected $fillable = ['user_id', 'query', 'normalized_query', 'searched_at'];

    protected function casts(): array
    {
        return ['searched_at' => 'datetime'];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
