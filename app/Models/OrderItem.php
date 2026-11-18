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
    'sku', 'name', 'variant_name', 'price', 'quantity', 'line_total', 'indent_quantity',
])]
class OrderItem extends Model
{
    use HasFactory;

    protected $fillable = [
        'order_id', 'part_id', 'part_variant_id',
        'itemable_type', 'itemable_id',
        'sku', 'name', 'variant_name', 'price', 'quantity', 'line_total', 'indent_quantity',
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

    public function isIndent(): bool
    {
        return (int) ($this->indent_quantity ?? 0) > 0;
    }

    public function isPart(): bool
    {
        if ($this->part_id !== null || $this->part_variant_id !== null) {
            return true;
        }

        if ($this->itemable_type === PartVariant::class || $this->itemable_type === Part::class) {
            return true;
        }

        $itemable = $this->itemable;
        if ($itemable instanceof PartVariant || $itemable instanceof Part) {
            return true;
        }

        if ($itemable instanceof ItemColor) {
            $item = $itemable->item;
            if ($item && ($item->type?->slug === 'sparepart' || $item->type === 'sparepart')) {
                return true;
            }
        }

        if ($itemable instanceof Item) {
            if ($itemable->type?->slug === 'sparepart' || $itemable->type === 'sparepart') {
                return true;
            }
        }

        return false;
    }

    public function isVehicle(): bool
    {
        return ! $this->isPart();
    }
}
