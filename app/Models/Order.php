<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

#[Fillable([
    'user_id',
    'order_no',
    'status',
    'subtotal',
    'shipping_cost',
    'total',
    'address_snapshot',
    'shipping_snapshot',
])]
class Order extends Model
{
    use HasFactory;

        protected $fillable = ['user_id', 'order_no', 'status', 'payment_status', 'payment_method', 'payment_provider', 'payment_reference', 'paid_at', 'subtotal', 'shipping_cost', 'total', 'address_snapshot', 'shipping_snapshot', 'shipping_courier', 'shipping_receipt', 'shipped_at', 'completed_at', 'cancelled_at'];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total' => 'decimal:2',
            'address_snapshot' => 'array',
            'shipping_snapshot' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function items(): HasMany
    {
        return $this->hasMany(OrderItem::class);
    }

    public function payment(): HasOne
    {
        return $this->hasOne(Payment::class);
    }

    public function shipment(): HasOne
    {
        return $this->hasOne(Shipment::class);
    }
}
