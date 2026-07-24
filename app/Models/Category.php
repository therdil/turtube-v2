<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
])]
class Category extends Model
{
    /**
     * Route Model Binding için slug kullan.
     */
    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    /**
     * Bu kategoriye ait videolar.
     */
    public function videos(): HasMany
    {
        return $this->hasMany(Video::class);
    }
}