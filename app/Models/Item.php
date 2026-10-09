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
        'price', 'discount_type', 'discount_value', 'discount_price',
        'thumbnail_path', 'document_path', 'part_number', 'catalog_pdf_path',
        'stock', 'stock_status', 'stock_updated_at', 'status', 'is_active', 'sort_order',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'discount_type' => 'string',
            'discount_value' => 'decimal:2',
            'discount_price' => 'decimal:2',
            'is_active' => 'boolean',
            'stock_updated_at' => 'datetime',
        ];
    }

    public function getFinalPriceAttribute(): decimal
    {
        $discountType = $this->discount_type;
        $discountValue = $this->discount_value;
        $price = $this->price;

        if (!$discountType || !$discountValue || $price <= 0) {
            return $price;
        }

        $finalPrice = $price;

        if ($discountType === 'percent') {
            $finalPrice = $price - ($price * $discountValue / 100);
        } elseif ($discountType === 'fixed') {
            $finalPrice = max(0, $price - $discountValue);
        }

        return $finalPrice;
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

    /**
     * Motor yang cocok/kompatibel dengan sparepart ini (untuk item bertipe sparepart).
     */
    public function compatibleMotors(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'item_motor_compatibility', 'item_id', 'motor_id')->withTimestamps();
    }

    /**
     * Sparepart yang kompatibel dengan motor ini.
     */
    public function compatibleSpareparts(): BelongsToMany
    {
        return $this->belongsToMany(Item::class, 'item_motor_compatibility', 'motor_id', 'item_id')->withTimestamps();
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
