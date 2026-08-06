<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

#[Fillable(['part_id', 'sku', 'name', 'price', 'stock', 'weight', 'is_default'])]
class PartVariant extends Model
{
    use HasFactory;

        protected $fillable = ['part_id', 'sku', 'name', 'price', 'stock', 'weight', 'is_default', 'stock_updated_at'];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'is_default' => 'boolean',
        ];
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }

    public function cartItems(): MorphMany
    {
        return $this->morphMany(CartItem::class, 'itemable');
    }

    public function orderItems(): MorphMany
    {
        return $this->morphMany(OrderItem::class, 'itemable');
    }

    public function stockMutations(): MorphMany
    {
        return $this->morphMany(StockMutation::class, 'stockable');
    }
}
