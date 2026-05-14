<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'name',
    'slug',
    'year',
    'thumbnail_path',
    'short_description',
    'description',
    'status',
])]
class Motor extends Model
{
    use HasFactory;

    public function images(): HasMany
    {
        return $this->hasMany(MotorImage::class)->orderBy('sort_order');
    }

    public function parts(): BelongsToMany
    {
        return $this->belongsToMany(Part::class)->withTimestamps();
    }
}
