<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Illuminate\Support\Carbon;

#[Fillable([
    'user_id',
    'order_no',
    'status',
    'is_admin_verified',
    'admin_verified_at',
    'admin_verified_by',
    'payment_status',
    'payment_method',
    'purchase_type',
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
    'invoice_no',
    'invoice_date',
    'receipt_no',
    'receipt_date',
    'receipt_payment_for',
    'spk_no',
    'spk_date',
    'spk_order_date',
    'stnk_name',
    'stnk_address',
    'motor_color',
    'manufacturing_year',
    'spk_payment_type',
    'leasing_provider',
    'spk_dp_amount',
    'credit_tenor',
    'monthly_installment',
    'booking_fee',
    'spk_checklist_docs',
    'spk_notes',
    'chassis_no',
    'engine_no',
    'buyer_nik',
    'buyer_name',
    'buyer_address',
    'notes',
    'signatory_name',
])]
class Order extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id', 'order_no', 'status', 'is_admin_verified', 'admin_verified_at', 'admin_verified_by',
        'payment_status', 'payment_method', 'purchase_type', 'payment_provider', 'payment_reference', 'paid_at',
        'subtotal', 'shipping_cost', 'shipping_type', 'total',
        'dp_amount', 'remaining_amount', 'is_indent', 'indent_status',
        'address_snapshot', 'shipping_snapshot', 'shipping_courier', 'shipping_receipt',
        'shipped_at', 'completed_at', 'cancelled_at',
        'invoice_no', 'invoice_date', 'receipt_no', 'receipt_date', 'receipt_payment_for',
        'spk_no', 'spk_date', 'spk_order_date', 'stnk_name', 'stnk_address', 'motor_color', 'manufacturing_year',
        'spk_payment_type', 'leasing_provider', 'spk_dp_amount', 'credit_tenor',
        'monthly_installment', 'booking_fee', 'spk_checklist_docs', 'spk_notes',
        'chassis_no', 'engine_no', 'buyer_nik',
        'buyer_name', 'buyer_address', 'notes', 'signatory_name',
    ];

    public const STATUSES = ['unpaid', 'paid', 'processing', 'shipped', 'completed', 'cancelled'];

    public const SHIPPING_TYPE_COURIER = 'courier';

    public const SHIPPING_TYPE_DEALER_PICKUP = 'dealer_pickup';

    public const PAYMENT_STATUSES = ['pending', 'paid', 'failed', 'expired'];

    public const INDENT_STATUSES = ['waiting_stock', 'ready_for_delivery', 'waiting_payment', 'paid_full'];
    public const PURCHASE_TYPE_CASH = 'cash';
    public const PURCHASE_TYPE_CREDIT = 'credit';

    public const DEFAULT_MOTOR_INVOICE_NOTES = "Harga pada Dokumen ini mengacu pada harga Jual (On The Road),\nHarga sudah termasuk PPN 11%\nDokumen ini bukan merupakan tanda terima Uang,\nPembayaran dianggap SAH apabila Keseluruhan Uang telah diterima di Rekening Resmi atas nama CV. Jowimar Mandiri Makmur.";

    protected function casts(): array
    {
        return [
            'is_admin_verified' => 'boolean',
            'admin_verified_at' => 'datetime',
            'admin_verified_by' => 'integer',
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
            'invoice_date' => 'date',
            'receipt_date' => 'date',
            'spk_date' => 'date',
            'spk_order_date' => 'date',
            'spk_dp_amount' => 'decimal:2',
            'monthly_installment' => 'decimal:2',
            'booking_fee' => 'decimal:2',
            'spk_checklist_docs' => 'array',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function adminVerifier(): BelongsTo
    {
        return $this->belongsTo(User::class, 'admin_verified_by');
    }

    public function vehicleItems()
    {
        return $this->items->filter(fn (OrderItem $item) => $item->isVehicle());
    }

    public function partItems()
    {
        return $this->items->filter(fn (OrderItem $item) => $item->isPart());
    }

    public function hasVehicle(): bool
    {
        return $this->vehicleItems()->isNotEmpty();
    }

    public function hasParts(): bool
    {
        return $this->partItems()->isNotEmpty();
    }

    public function isDocumentsVerified(): bool
    {
        return (bool) $this->is_admin_verified;
    }

    public static function generateMotorInvoiceNumber(): string
    {
        $year = now()->format('Y');
        $pattern = "%/FPSM-T/{$year}";

        $lastInvoice = self::where('invoice_no', 'like', $pattern)
            ->orderBy('invoice_no', 'desc')
            ->value('invoice_no');

        $seq = 1;
        if ($lastInvoice && preg_match('/^(\d+)\/FPSM-T\/' . $year . '$/', $lastInvoice, $m)) {
            $seq = (int) $m[1] + 1;
        }

        return sprintf('%03d/FPSM-T/%s', $seq, $year);
    }

    /**
     * SPK (Surat Pemesanan Motor) data: stored values merged with sensible defaults
     */
    public function spkData(): array
    {
        $item = $this->vehicleItems()->first() ?? $this->items->first();
        $snapshot = $this->address_snapshot ?? [];

        $buyerName = $this->buyer_name ?: ($snapshot['recipient_name'] ?? $this->user?->name ?? '-');
        $buyerPhone = $snapshot['phone'] ?? $this->user?->phone ?? '-';
        $buyerAddress = $this->buyer_address ?: collect([
            $snapshot['address_line1'] ?? '',
            $snapshot['address_line2'] ?? '',
            $snapshot['city'] ?? '',
            $snapshot['province'] ?? '',
            $snapshot['postal_code'] ?? '',
        ])->filter()->implode(', ');

        $modelName = $item ? ($item->name.($item->variant_name ? ' '.$item->variant_name : '')) : 'Sepeda Motor';
        $subtotal = $item ? (float) $item->line_total : (float) $this->total;
        $discount = (float) ($this->discount_amount ?? 0);
        $afterDiscount = max(0, $subtotal - $discount);

        return [
            'spk_no' => $this->spk_no ?: $this->order_no,
            'spk_date' => $this->spk_date ? Carbon::parse($this->spk_date)->toDateString() : ($this->created_at ? Carbon::parse($this->created_at)->toDateString() : now()->toDateString()),
            'spk_order_date' => $this->spk_order_date ? Carbon::parse($this->spk_order_date)->toDateString() : ($this->spk_date ? Carbon::parse($this->spk_date)->toDateString() : ($this->created_at ? Carbon::parse($this->created_at)->toDateString() : now()->toDateString())),
            'buyer_name' => $buyerName,
            'buyer_phone' => $buyerPhone,
            'stnk_name' => $this->stnk_name ?: $buyerName,
            'stnk_address' => $this->stnk_address ?: $buyerAddress,
            'buyer_nik' => $this->buyer_nik ?: '-',
            'motor_model' => $modelName,
            'motor_color' => $this->motor_color ?: ($item?->variant_name ?: '-'),
            'manufacturing_year' => $this->manufacturing_year ?: date('Y'),
            'otr_price' => $subtotal,
            'discount' => $discount,
            'price_after_discount' => $afterDiscount,
            'payment_type' => $this->spk_payment_type ?: 'cash',
            'leasing_provider' => $this->leasing_provider,
            'dp_amount' => $this->spk_dp_amount !== null ? (float) $this->spk_dp_amount : (float) ($this->dp_amount ?? 0),
            'credit_tenor' => $this->credit_tenor,
            'monthly_installment' => $this->monthly_installment !== null ? (float) $this->monthly_installment : null,
            'booking_fee' => $this->booking_fee !== null ? (float) $this->booking_fee : 500000,
            'checklist_docs' => is_array($this->spk_checklist_docs) ? $this->spk_checklist_docs : ['ktp_suami', 'kartu_keluarga', 'slip_gaji', 'rekening_listrik', 'bukti_pbb'],
            'spk_notes' => $this->spk_notes ?: '',
            'seller_name' => $this->signatory_name ?: 'Syati',
        ];
    }

    /**
     * Receipt data: stored values merged with defaults
     */
    public function receiptData(): array
    {
        $item = $this->vehicleItems()->first() ?? $this->items->first();
        $itemName = $item ? ($item->name.($item->variant_name ? ' '.$item->variant_name : '')) : 'Unit Sepeda Motor';
        $defaultPaymentFor = 'Pelunasan '.$itemName;

        $snapshot = $this->address_snapshot ?? [];
        $buyerName = $this->buyer_name ?: ($snapshot['recipient_name'] ?? $this->user?->name);

        return [
            'receipt_no' => $this->receipt_no ?: $this->order_no,
            'receipt_date' => $this->receipt_date ? Carbon::parse($this->receipt_date)->toDateString() : ($this->paid_at ? Carbon::parse($this->paid_at)->toDateString() : now()->toDateString()),
            'received_from' => $buyerName,
            'amount' => (float) $this->total,
            'amount_words' => terbilang((float) $this->total),
            'payment_for' => $this->receipt_payment_for ?: $defaultPaymentFor,
            'chassis_no' => $this->chassis_no,
            'engine_no' => $this->engine_no,
            'signatory_name' => $this->signatory_name ?: 'JONI',
        ];
    }

    /**
     * Motor invoice data: stored values merged with sensible defaults
     * (auto-fill from the order) for every field not yet filled in.
     */
    public function motorInvoiceData(): array
    {
        $snapshot = $this->address_snapshot ?? [];

        $data = [
            'invoice_no' => $this->invoice_no ?: self::generateMotorInvoiceNumber(),
            'invoice_date' => now()->toDateString(),
            'chassis_no' => null,
            'engine_no' => null,
            'buyer_nik' => null,
            'buyer_name' => $snapshot['recipient_name'] ?? $this->user?->name,
            'buyer_address' => collect([
                $snapshot['address_line1'] ?? '',
                $snapshot['address_line2'] ?? '',
                $snapshot['city'] ?? '',
                $snapshot['province'] ?? '',
                $snapshot['postal_code'] ?? '',
            ])->filter()->implode(', ') ?: null,
            'notes' => self::DEFAULT_MOTOR_INVOICE_NOTES,
            'signatory_name' => 'JONI',
        ];

        foreach ($this->only(array_keys($data)) as $key => $value) {
            if ($value !== null && $value !== '') {
                $data[$key] = $value;
            }
        }

        $data['invoice_date'] = Carbon::parse($data['invoice_date'])->toDateString();

        return $data;
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

    public function creditApplication(): HasOne
    {
        return $this->hasOne(CreditApplication::class);
    }

    /** Pembelian secara kredit (leasing): DP dari customer, pelunasan dari leasing. */
    public function isCredit(): bool
    {
        return $this->purchase_type === self::PURCHASE_TYPE_CREDIT;
    }

    public function purchaseTypeLabel(): string
    {
        return match ($this->purchase_type) {
            self::PURCHASE_TYPE_CREDIT => 'Kredit',
            default => 'Tunai',
        };
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
    public function paymentExpiresAt(): Carbon
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
