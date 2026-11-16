<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Satu catatan pembayaran pada pengajuan kredit.
 *
 * DP dibayarkan customer langsung ke dealer (payer_type = customer),
 * pelunasan dibayarkan pihak leasing ke dealer (payer_type = leasing).
 * Keduanya dicatat terpisah — dibatasi unique (credit_application_id, type).
 */
#[Fillable([
    'credit_application_id',
    'type',
    'payer_type',
    'amount',
    'paid_at',
    'method',
    'reference',
    'notes',
    'recorded_by',
])]
class CreditPayment extends Model
{
    use HasFactory;

    public const TYPE_DP = 'dp';
    public const TYPE_PELUNASAN = 'pelunasan';

    public const PAYER_CUSTOMER = 'customer';
    public const PAYER_LEASING = 'leasing';

    public const METHODS = ['cash' => 'Tunai', 'transfer' => 'Transfer', 'qris' => 'QRIS', 'other' => 'Lainnya'];

    protected $fillable = [
        'credit_application_id', 'type', 'payer_type', 'amount', 'paid_at',
        'method', 'reference', 'notes', 'recorded_by',
    ];

    protected function casts(): array
    {
        return [
            'amount' => 'decimal:2',
            'paid_at' => 'datetime',
        ];
    }

    public function creditApplication(): BelongsTo
    {
        return $this->belongsTo(CreditApplication::class);
    }

    public function recordedBy(): BelongsTo
    {
        return $this->belongsTo(User::class, 'recorded_by');
    }

    public function typeLabel(): string
    {
        return match ($this->type) {
            self::TYPE_DP => 'DP',
            self::TYPE_PELUNASAN => 'Pelunasan',
            default => $this->type,
        };
    }

    public function payerLabel(): string
    {
        return match ($this->payer_type) {
            self::PAYER_CUSTOMER => 'Customer',
            self::PAYER_LEASING => 'Leasing',
            default => $this->payer_type,
        };
    }

    public function methodLabel(): string
    {
        return self::METHODS[$this->method] ?? ($this->method ?: '-');
    }
}
