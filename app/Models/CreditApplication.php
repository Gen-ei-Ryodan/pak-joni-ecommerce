<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Pengajuan pembelian motor secara kredit (leasing).
 *
 * Timeline/tahapan mengikuti alur pembelian kredit dealer:
 * SPK → Survey → Persetujuan Survey → Persiapan Unit → Pembayaran → Pengambilan Unit.
 * `stage` selalu menyimpan tahapan yang SEDANG berjalan (timestamp tahapan
 * sebelumnya sudah terisi), sehingga dapat difilter/ditampilkan di admin.
 */
#[Fillable([
    'order_id',
    'application_no',
    'leasing_name',
    'po_number',
    'po_date',
    'dp_amount',
    'stage',
    'status',
    'spk_at',
    'survey_at',
    'survey_approved_at',
    'unit_preparation_at',
    'payment_at',
    'unit_pickup_at',
    'notes',
])]
class CreditApplication extends Model
{
    use HasFactory;

    /** Urutan tahapan pembelian kredit (key = kolom timestamp '{key}_at'). */
    public const STAGES = [
        'spk' => 'SPK',
        'survey' => 'Survey',
        'survey_approved' => 'Persetujuan Survey',
        'unit_preparation' => 'Persiapan Unit',
        'payment' => 'Pembayaran',
        'unit_pickup' => 'Pengambilan Unit',
    ];

    public const STAGE_DESCRIPTIONS = [
        'spk' => 'Pembuatan dan penerbitan Surat Pemesanan Kendaraan',
        'survey' => 'Proses survey customer oleh pihak leasing',
        'survey_approved' => 'Hasil survey dan persetujuan pengajuan kredit',
        'unit_preparation' => 'Dealer mulai menyiapkan unit motor',
        'payment' => 'Proses pembayaran DP dan pelunasan',
        'unit_pickup' => 'Customer melakukan pengambilan motor',
    ];

    public const STATUS_IN_PROGRESS = 'in_progress';
    public const STATUS_COMPLETED = 'completed';
    public const STATUS_REJECTED = 'rejected';

    protected $fillable = [
        'order_id', 'application_no', 'leasing_name', 'po_number', 'po_date',
        'dp_amount', 'stage', 'status',
        'spk_at', 'survey_at', 'survey_approved_at', 'unit_preparation_at',
        'payment_at', 'unit_pickup_at',
        'notes',
    ];

    protected function casts(): array
    {
        return [
            'dp_amount' => 'decimal:2',
            'po_date' => 'date',
            'spk_at' => 'datetime',
            'survey_at' => 'datetime',
            'survey_approved_at' => 'datetime',
            'unit_preparation_at' => 'datetime',
            'payment_at' => 'datetime',
            'unit_pickup_at' => 'datetime',
        ];
    }

    public function order(): BelongsTo
    {
        return $this->belongsTo(Order::class);
    }

    public function payments(): HasMany
    {
        return $this->hasMany(CreditPayment::class);
    }

    /** Kolom timestamp untuk sebuah tahapan. */
    public static function stageTimestampColumn(string $stage): string
    {
        return $stage.'_at';
    }

    public static function stageLabelStatic(string $stage): string
    {
        return self::STAGES[$stage] ?? $stage;
    }

    public function stageLabel(): string
    {
        return self::stageLabelStatic($this->stage);
    }

    public static function stageDescription(string $stage): string
    {
        return self::STAGE_DESCRIPTIONS[$stage] ?? '';
    }

    public function statusLabel(): string
    {
        return match ($this->status) {
            self::STATUS_IN_PROGRESS => 'Diproses',
            self::STATUS_COMPLETED => 'Selesai',
            self::STATUS_REJECTED => 'Ditolak',
            default => $this->status,
        };
    }

    public function statusBadge(): string
    {
        return match ($this->status) {
            self::STATUS_IN_PROGRESS => 'bg-yellow',
            self::STATUS_COMPLETED => 'bg-green',
            self::STATUS_REJECTED => 'bg-red',
            default => 'bg-gray',
        };
    }

    public function stageBadge(): string
    {
        return match ($this->stage) {
            'spk' => 'bg-gray',
            'survey' => 'bg-blue',
            'survey_approved' => 'bg-purple',
            'unit_preparation' => 'bg-orange',
            'payment' => 'bg-yellow',
            'unit_pickup' => 'bg-green',
            default => 'bg-gray',
        };
    }

    /**
     * Tahapan berikutnya yang harus diselesaikan (timestamp pertama yang kosong).
     * null = seluruh tahapan sudah selesai.
     */
    public function currentStage(): ?string
    {
        foreach (array_keys(self::STAGES) as $stage) {
            if (! $this->{self::stageTimestampColumn($stage)}) {
                return $stage;
            }
        }

        return null;
    }

    public function isStageDone(string $stage): bool
    {
        return (bool) $this->{self::stageTimestampColumn($stage)};
    }

    /** Data timeline untuk ditampilkan (buyer & admin). */
    public function timeline(): array
    {
        $current = $this->currentStage();

        return collect(self::STAGES)
            ->map(fn (string $label, string $key) => [
                'key' => $key,
                'label' => $label,
                'description' => self::STAGE_DESCRIPTIONS[$key] ?? '',
                'done' => $this->isStageDone($key),
                'current' => $key === $current && $this->status === self::STATUS_IN_PROGRESS,
                'time' => $this->{self::stageTimestampColumn($key)},
            ])
            ->values()
            ->all();
    }

    // Query-based agar selalu membaca data terbaru (tidak pakai cache relasi).
    public function dpPayment(): ?CreditPayment
    {
        return $this->payments()->where('type', CreditPayment::TYPE_DP)->first();
    }

    public function pelunasanPayment(): ?CreditPayment
    {
        return $this->payments()->where('type', CreditPayment::TYPE_PELUNASAN)->first();
    }

    public function totalPaid(): float
    {
        return (float) $this->payments()->sum('amount');
    }

    public function sisaAmount(): float
    {
        return max(0, (float) $this->order?->total - $this->totalPaid());
    }

    public function isRejected(): bool
    {
        return $this->status === self::STATUS_REJECTED;
    }

    public function isCompleted(): bool
    {
        return $this->status === self::STATUS_COMPLETED;
    }
}
