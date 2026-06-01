<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable([
    'brand_id',
    'category_id',
    'name',
    'slug',
    'year',
    'price',
    'thumbnail_path',
    'short_description',
    'description',
    'status',
])]
class Motor extends Model
{
    use HasFactory;

    protected $fillable = ['brand_id', 'category_id', 'name', 'slug', 'year', 'price', 'thumbnail_path', 'short_description', 'description', 'status'];

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(MotorCategory::class, 'category_id');
    }

    public function images(): HasMany
    {
        return $this->hasMany(MotorImage::class)->orderBy('sort_order');
    }

    public function colors(): HasMany
    {
        return $this->hasMany(MotorColor::class)->orderBy('sort_order');
    }

    public function specifications(): HasMany
    {
        return $this->hasMany(MotorSpecification::class)->orderBy('sort_order');
    }

    public function images360(): HasMany
    {
        return $this->hasMany(Motor360Image::class)->orderBy('sort_order');
    }

    public function parts(): BelongsToMany
    {
        return $this->belongsToMany(Part::class)->withTimestamps();
    }

    public function priceLists(): HasMany
    {
        return $this->hasMany(PriceList::class)->orderBy('sort_order');
    }

    public function partCatalogs(): HasMany
    {
        return $this->hasMany(PartCatalog::class)->orderBy('sort_order');
    }
}
