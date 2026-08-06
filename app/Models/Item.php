<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class Item extends Model
{
    use HasFactory;

    protected $fillable = [
        'category_type_id', 'brand_id', 'category_id',
        'name', 'slug', 'year', 'description', 'short_description',
        'price', 'thumbnail_path', 'stock', 'stock_status',
        'stock_updated_at', 'status', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_active' => 'boolean',
            'stock_updated_at' => 'datetime',
        ];
    }

    public function type(): BelongsTo
    {
        return $this->belongsTo(CategoryType::class, 'category_type_id');
    }

    public function brand(): BelongsTo
    {
        return $this->belongsTo(Brand::class);
    }

    public function category(): BelongsTo
    {
        return $this->belongsTo(Category::class, 'category_id');
    }

    public function parts(): BelongsToMany
    {
        return $this->belongsToMany(Part::class)->withTimestamps();
    }

    public function images(): HasMany
    {
        return $this->hasMany(ItemImage::class)->orderBy('sort_order');
    }

    public function colors(): HasMany
    {
        return $this->hasMany(ItemColor::class)->orderBy('sort_order');
    }

    public function specifications(): HasMany
    {
        return $this->hasMany(ItemSpecification::class)->orderBy('sort_order');
    }

    public function images360(): HasMany
    {
        return $this->hasMany(Item360Image::class)->orderBy('sort_order');
    }

    public function priceLists(): HasMany
    {
        return $this->hasMany(ItemPriceList::class)->orderBy('sort_order');
    }

    public function partCatalogs(): HasMany
    {
        return $this->hasMany(ItemPartCatalog::class)->orderBy('sort_order');
    }

    public function stockMutations(): MorphMany
    {
        return $this->morphMany(StockMutation::class, 'stockable');
    }
}
