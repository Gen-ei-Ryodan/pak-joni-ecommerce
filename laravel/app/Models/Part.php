<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'sku',
    'name',
    'slug',
    'part_category_id',
    'thumbnail_path',
    'short_description',
    'description',
    'specification',
    'base_price',
    'status',
])]
class Part extends Model
{
    use HasFactory;

    protected function casts(): array
    {
        return [
            'base_price' => 'decimal:2',
        ];
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(PartCategory::class, 'part_category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(PartImage::class)->orderBy('sort_order');
    }

    public function variants(): HasMany
    {
        return $this->hasMany(PartVariant::class)->orderByDesc('is_default')->orderBy('name');
    }

    public function defaultVariant(): HasOne
    {
        return $this->hasOne(PartVariant::class)->where('is_default', true);
    }

    public function motors(): BelongsToMany
    {
        return $this->belongsToMany(Motor::class)->withTimestamps();
    }
}
