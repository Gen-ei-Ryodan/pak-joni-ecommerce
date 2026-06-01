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
    'payment_status',
    'payment_method',
    'payment_provider',
    'payment_reference',
    'paid_at',
    'subtotal',
    'shipping_cost',
    'total',
    'address_snapshot',
    'shipping_snapshot',
    'shipping_courier',
    'shipping_receipt',
    'shipped_at',
    'completed_at',
    'cancelled_at',
])]
class Order extends Model
{
    use HasFactory;

        protected $fillable = ['user_id', 'order_no', 'status', 'payment_status', 'payment_method', 'payment_provider', 'payment_reference', 'paid_at', 'subtotal', 'shipping_cost', 'total', 'address_snapshot', 'shipping_snapshot', 'shipping_courier', 'shipping_receipt', 'shipped_at', 'completed_at', 'cancelled_at'];

    public const STATUSES = ['unpaid', 'paid', 'processing', 'shipped', 'completed', 'cancelled'];

    public const PAYMENT_STATUSES = ['pending', 'paid', 'failed', 'expired'];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total' => 'decimal:2',
            'address_snapshot' => 'array',
            'shipping_snapshot' => 'array',
            'paid_at' => 'datetime',
            'shipped_at' => 'datetime',
            'completed_at' => 'datetime',
            'cancelled_at' => 'datetime',
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

    public function statusBadge(): string
    {
        return match ($this->status) {
            'unpaid' => 'bg-yellow',
            'paid' => 'bg-blue',
            'processing' => 'bg-purple',
            'shipped' => 'bg-orange',
            'completed' => 'bg-green',
            'cancelled' => 'bg-red',
            default => 'bg-gray',
        };
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            'unpaid' => 'Belum Dibayar',
            'paid' => 'Dibayar',
            'processing' => 'Diproses',
            'shipped' => 'Dikirim',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => $this->status,
        };
    }

    public function paymentStatusBadge(): string
    {
        return match ($this->payment_status) {
            'pending' => 'bg-yellow',
            'paid' => 'bg-green',
            'failed' => 'bg-red',
            'expired' => 'bg-gray',
            default => 'bg-gray',
        };
    }

    public function canTransitionTo(string $targetStatus): bool
    {
        $transitions = [
            'unpaid' => ['paid', 'cancelled'],
            'paid' => ['processing', 'cancelled'],
            'processing' => ['shipped', 'cancelled'],
            'shipped' => ['completed'],
            'completed' => [],
            'cancelled' => [],
        ];

        return in_array($targetStatus, $transitions[$this->status] ?? []);
    }
}
