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
    'shipping_type',
    'total',
    'dp_amount',
    'remaining_amount',
    'is_indent',
    'indent_status',
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

    protected $fillable = [
        'user_id', 'order_no', 'status', 'payment_status',
        'payment_method', 'payment_provider', 'payment_reference', 'paid_at',
        'subtotal', 'shipping_cost', 'shipping_type', 'total',
        'dp_amount', 'remaining_amount', 'is_indent', 'indent_status',
        'address_snapshot', 'shipping_snapshot', 'shipping_courier', 'shipping_receipt',
        'shipped_at', 'completed_at', 'cancelled_at',
    ];

    public const STATUSES = ['unpaid', 'paid', 'processing', 'shipped', 'completed', 'cancelled'];
    public const SHIPPING_TYPE_COURIER = 'courier';
    public const SHIPPING_TYPE_DEALER_PICKUP = 'dealer_pickup';
    public const PAYMENT_STATUSES = ['pending', 'paid', 'failed', 'expired'];
    public const INDENT_STATUSES = ['waiting_stock', 'ready_for_delivery', 'waiting_payment', 'paid_full'];

    protected function casts(): array
    {
        return [
            'subtotal' => 'decimal:2',
            'shipping_cost' => 'decimal:2',
            'total' => 'decimal:2',
            'dp_amount' => 'decimal:2',
            'remaining_amount' => 'decimal:2',
            'is_indent' => 'boolean',
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

    public function isDealerPickup(): bool
    {
        return $this->shipping_type === self::SHIPPING_TYPE_DEALER_PICKUP;
    }

    public function shippingTypeLabel(): string
    {
        return match ($this->shipping_type) {
            self::SHIPPING_TYPE_DEALER_PICKUP => 'Ambil di Dealer',
            default => 'Dikirim via Kurir',
        };
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

    public function indentStatusLabel(): string
    {
        return static::indentStatusLabelStatic($this->indent_status);
    }

    public static function indentStatusLabelStatic(?string $status): string
    {
        return match ($status) {
            'waiting_stock' => 'Menunggu Stok',
            'ready_for_delivery' => 'Siap Dikirim',
            'waiting_payment' => 'Menunggu Pelunasan',
            'paid_full' => 'Lunas',
            default => '-',
        };
    }

    public function indentStatusBadge(): string
    {
        return match ($this->indent_status) {
            'waiting_stock' => 'bg-yellow',
            'ready_for_delivery' => 'bg-blue',
            'waiting_payment' => 'bg-orange',
            'paid_full' => 'bg-green',
            default => 'bg-gray',
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

    /**
     * Calculate payment expiry time (24 hours from creation).
     */
    public function paymentExpiresAt(): \Illuminate\Support\Carbon
    {
        return $this->created_at->addHours(24);
    }

    /**
     * Check if payment window has expired.
     */
    public function isPaymentExpired(): bool
    {
        return now()->greaterThan($this->paymentExpiresAt());
    }

    /**
     * Get remaining payment time in seconds.
     */
    public function paymentRemainingSeconds(): int
    {
        return max(0, now()->diffInSeconds($this->paymentExpiresAt(), false));
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
