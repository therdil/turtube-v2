<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Video extends Model
{
    protected $fillable = [
        'title',
        'description',
        'thumbnail',
        'video_path',
        'channel_name',
        'views',
        'duration',
        'user_id',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
}