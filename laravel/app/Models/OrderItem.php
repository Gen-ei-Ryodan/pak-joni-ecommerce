<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphTo;

#[Fillable([
    'order_id', 'part_id', 'part_variant_id',
    'itemable_type', 'itemable_id',
    'sku', 'name', 'variant_name', 'price', 'quantity', 'line_total',
])]
class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'part_id', 'part_variant_id',
        'itemable_type', 'itemable_id',
        'sku', 'name', 'variant_name', 'price', 'quantity', 'line_total',
    ];

    protected function casts(): array
    {
        return [
            'price' => 'decimal:2',
            'line_total' => 'decimal:2',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
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
