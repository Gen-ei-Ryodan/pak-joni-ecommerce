<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable(['cart_id', 'part_variant_id', 'itemable_type', 'itemable_id', 'quantity', 'price_snapshot', 'product_name', 'variant_name', 'image_path'])]
class CartItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'cart_id', 'part_variant_id',
        'itemable_type', 'itemable_id',
        'quantity', 'indent_quantity', 'price_snapshot',
        'product_name', 'variant_name', 'image_path',
    ];

    protected function casts(): array
    {
        return [
            'price_snapshot' => 'decimal:2',
        ];
    }

    public function cart(): BelongsTo
    {
        return $this->belongsTo(Cart::class);
    }

    /** @deprecated gunakan itemable() */
    public function variant(): BelongsTo
    {
        return $this->belongsTo(PartVariant::class, 'part_variant_id');
    }

    public function itemable(): MorphTo
    {
        return $this->morphTo();
    }
}
