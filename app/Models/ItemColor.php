<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class ItemColor extends Model
{
    use HasFactory;

    protected $fillable = ['item_id', 'name', 'color_code', 'image_path', 'weight', 'sort_order', 'stock', 'stock_updated_at'];

    protected function casts(): array
    {
        return [
            'stock' => 'integer',
            'stock_updated_at' => 'datetime',
        ];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
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
