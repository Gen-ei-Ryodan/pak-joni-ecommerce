<?php

namespace App\Services;

use App\Exceptions\CreditException;
use App\Models\CreditApplication;
use App\Models\CreditPayment;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

/**
 * Logika bisnis pembelian kredit (leasing).
 *
 * Membagi pembayaran menjadi dua pihak:
 * - DP    → dibayarkan customer langsung ke dealer (dicatat lebih dulu).
 * - Pelunasan → dibayarkan pihak leasing ke dealer.
 *
 * Timeline tahapan: SPK → Survey → Persetujuan Survey → Persiapan Unit
 * → Pembayaran → Pengambilan Unit. Tahapan hanya boleh diselesaikan
 * secara berurutan (tidak boleh melompat).
 */
class CreditService
{
    public function __construct(
        protected OrderService $orderService,
    ) {}

    /**
     * Buat pengajuan kredit untuk sebuah order (dipanggil saat checkout kredit).
     */
    public function createForOrder(Order $order): CreditApplication
    {
        $existing = $order->creditApplication;
        if ($existing) {
            return $existing;
        }

        return CreditApplication::create([
            'order_id' => $order->id,
            'application_no' => $this->newApplicationNo(),
            'stage' => 'spk',
            'status' => CreditApplication::STATUS_IN_PROGRESS,
        ]);
    }

    /**
     * Tahap 1 — terbitkan SPK (Surat Pemesanan Kendaraan).
     */
    public function issueSpk(CreditApplication $app, ?string $notes = null): CreditApplication
    {
        $this->assertInProgress($app);
        $this->assertStage($app, 'spk');

        return $this->completeStage($app, 'spk', $notes);
    }

    /**
     * Tahap 2 — survey oleh pihak leasing selesai dilakukan.
     */
    public function recordSurvey(CreditApplication $app, ?string $notes = null): CreditApplication
    {
        $this->assertInProgress($app);
        $this->assertStage($app, 'survey');

        return $this->completeStage($app, 'survey', $notes);
    }

    /**
     * Tahap 3 — hasil survey disetujui.
     */
    public function approveSurvey(CreditApplication $app, ?string $notes = null): CreditApplication
    {
        $this->assertInProgress($app);
        $this->assertStage($app, 'survey_approved');

        return $this->completeStage($app, 'survey_approved', $notes);
    }

    /**
     * Tahap 3 (alternatif) — pengajuan kredit ditolak setelah survey.
     */
    public function rejectSurvey(CreditApplication $app, ?string $notes = null): CreditApplication
    {
        $this->assertInProgress($app);
        $this->assertStage($app, 'survey_approved');

        DB::transaction(function () use ($app, $notes) {
            $app->status = CreditApplication::STATUS_REJECTED;
            $this->appendNotes($app, $notes);
            $app->save();
        });

        return $app->refresh();
    }

    /**
     * Catat PO dari leasing: nominal DP yang harus dibayarkan customer ke dealer.
     * Bisa dipakai ulang untuk memperbaiki data selama DP belum tercatat.
     */
    public function recordPo(CreditApplication $app, array $data): CreditApplication
    {
        $this->assertInProgress($app);

        if ($app->dpPayment()) {
            throw new CreditException('DP sudah tercatat, PO leasing tidak dapat diubah lagi.');
        }

        if (! $app->survey_approved_at) {
            throw new CreditException('Persetujuan survey belum dilakukan, PO leasing belum dapat dicatat.');
        }

        $leasingName = trim((string) ($data['leasing_name'] ?? ''));
        $poNumber = trim((string) ($data['po_number'] ?? ''));
        $poDate = $data['po_date'] ?? null;
        $dpAmount = (float) ($data['dp_amount'] ?? 0);
        $total = (float) $app->order?->total;

        if ($leasingName === '') {
            throw new CreditException('Nama leasing wajib diisi.');
        }
        if ($poNumber === '') {
            throw new CreditException('Nomor PO leasing wajib diisi.');
        }
        if (! $poDate) {
            throw new CreditException('Tanggal PO leasing wajib diisi.');
        }
        if ($dpAmount <= 0) {
            throw new CreditException('Nominal DP harus lebih dari 0.');
        }
        if ($dpAmount > $total) {
            throw new CreditException('Nominal DP melebihi total pesanan (Rp '.number_format($total, 0, ',', '.').').');
        }

        $app->update([
            'leasing_name' => $leasingName,
            'po_number' => $poNumber,
            'po_date' => $poDate,
            'dp_amount' => $dpAmount,
        ]);

        return $app->refresh();
    }

    /**
     * Tahap 4 — unit disiapkan dealer.
     */
    public function prepareUnit(CreditApplication $app, ?string $notes = null): CreditApplication
    {
        $this->assertInProgress($app);
        $this->assertStage($app, 'unit_preparation');

        return $this->completeStage($app, 'unit_preparation', $notes);
    }

    /**
     * Tahap 5 — catat pembayaran DP dari customer (langsung ke dealer).
     * Order ditandai paid sehingga stok unit berkurang (satu unit untuk pengajuan ini).
     */
    public function recordDpPayment(CreditApplication $app, array $data, ?User $actor = null): CreditPayment
    {
        $this->assertInProgress($app);

        if ($app->dpPayment()) {
            throw new CreditException('Pembayaran DP sudah tercatat sebelumnya.');
        }
        if (! $app->unit_preparation_at) {
            throw new CreditException('Tahap "Persiapan Unit" belum selesai, pembayaran DP belum dapat dicatat.');
        }
        if ((float) $app->dp_amount <= 0) {
            throw new CreditException('Nominal DP dari leasing belum ditentukan. Catat PO leasing terlebih dahulu.');
        }

        $amount = $this->validateAmount($app, $data, 'DP');

        return DB::transaction(function () use ($app, $data, $actor, $amount) {
            $payment = CreditPayment::create([
                'credit_application_id' => $app->id,
                'type' => CreditPayment::TYPE_DP,
                'payer_type' => CreditPayment::PAYER_CUSTOMER,
                'amount' => $amount,
                'paid_at' => $data['paid_at'] ?? now(),
                'method' => $data['method'] ?? null,
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'recorded_by' => $actor?->id,
            ]);

            // Semua jalur menandai order paid WAJIB lewat OrderService::markAsPaid
            // agar penurunan stok selalu tercatat.
            $this->orderService->markAsPaid($app->order, [
                'payment_method' => 'credit',
            ]);

            $this->syncPaymentStage($app);

            return $payment;
        });
    }

    /**
     * Tahap 5 — catat pembayaran pelunasan dari pihak leasing ke dealer.
     */
    public function recordPelunasanPayment(CreditApplication $app, array $data, ?User $actor = null): CreditPayment
    {
        $this->assertInProgress($app);

        if ($app->pelunasanPayment()) {
            throw new CreditException('Pembayaran pelunasan sudah tercatat sebelumnya.');
        }
        if (! $app->dpPayment()) {
            throw new CreditException('DP dari customer belum tercatat, pelunasan leasing belum dapat dicatat.');
        }

        $amount = $this->validateAmount($app, $data, 'Pelunasan');

        return DB::transaction(function () use ($app, $data, $actor, $amount) {
            $payment = CreditPayment::create([
                'credit_application_id' => $app->id,
                'type' => CreditPayment::TYPE_PELUNASAN,
                'payer_type' => CreditPayment::PAYER_LEASING,
                'amount' => $amount,
                'paid_at' => $data['paid_at'] ?? now(),
                'method' => $data['method'] ?? null,
                'reference' => $data['reference'] ?? null,
                'notes' => $data['notes'] ?? null,
                'recorded_by' => $actor?->id,
            ]);

            $this->syncPaymentStage($app);

            return $payment;
        });
    }

    /**
     * Tahap 6 — customer mengambil unit, pengajuan kredit selesai.
     * Order ikut diselesaikan bila statusnya sudah siap dikirim/diambil.
     */
    public function confirmPickup(CreditApplication $app, ?string $notes = null): CreditApplication
    {
        $this->assertInProgress($app);
        $this->assertStage($app, 'unit_pickup');

        DB::transaction(function () use ($app, $notes) {
            $app->unit_pickup_at = now();
            $app->status = CreditApplication::STATUS_COMPLETED;
            $this->appendNotes($app, $notes);
            $app->save();
            $this->syncStage($app);

            $this->completeOrder($app->order);
        });

        return $app->refresh();
    }

    /**
     * Dipanggil saat customer mengonfirmasi penerimaan barang (alur kirim).
     * Melengkapi tahap akhir hanya bila seluruh tahapan sebelumnya sudah selesai.
     */
    public function confirmPickupIfReady(CreditApplication $app, ?string $notes = null): ?CreditApplication
    {
        if ($app->status !== CreditApplication::STATUS_IN_PROGRESS || $app->currentStage() !== 'unit_pickup') {
            return null;
        }

        return $this->confirmPickup($app, $notes);
    }

    /**
     * Tandai tahapan tertentu selesai (hanya tahapan berikutnya yang boleh).
     */
    private function completeStage(CreditApplication $app, string $stage, ?string $notes): CreditApplication
    {
        DB::transaction(function () use ($app, $stage, $notes) {
            $app->{CreditApplication::stageTimestampColumn($stage)} = now();
            $this->appendNotes($app, $notes);
            $app->save();
            $this->syncStage($app);
        });

        return $app->refresh();
    }

    /**
     * Tahap "Pembayaran" dianggap selesai saat DP dan pelunasan sama-sama tercatat.
     */
    private function syncPaymentStage(CreditApplication $app): void
    {
        $app->load('payments');

        if ($app->dpPayment() && $app->pelunasanPayment() && ! $app->payment_at) {
            $app->payment_at = now();
            $app->save();
            $this->syncStage($app);
        }
    }

    private function syncStage(CreditApplication $app): void
    {
        $current = $app->currentStage();
        $app->stage = $current ?? 'unit_pickup';
        $app->save();
    }

    private function assertInProgress(CreditApplication $app): void
    {
        if ($app->status !== CreditApplication::STATUS_IN_PROGRESS) {
            throw new CreditException('Pengajuan kredit sudah '.$app->statusLabel().', tidak dapat diperbarui.');
        }

        if ($app->order?->status === 'cancelled') {
            throw new CreditException('Pesanan terkait sudah dibatalkan.');
        }
    }

    private function assertStage(CreditApplication $app, string $expected): void
    {
        $current = $app->currentStage();

        if ($current === $expected) {
            return;
        }

        $expectedLabel = CreditApplication::stageLabelStatic($expected);
        $currentLabel = $current
            ? CreditApplication::stageLabelStatic($current)
            : 'seluruh tahapan sudah selesai';

        throw new CreditException('Tahap "'.$expectedLabel.'" tidak tersedia. Tahap saat ini: '.$currentLabel.'.');
    }

    private function validateAmount(CreditApplication $app, array $data, string $label): float
    {
        $amount = (float) ($data['amount'] ?? 0);
        $total = (float) $app->order?->total;

        if ($amount <= 0) {
            throw new CreditException('Nominal pembayaran '.$label.' harus lebih dari 0.');
        }

        $alreadyPaid = $app->totalPaid();
        if ($alreadyPaid + $amount > $total + 0.01) {
            throw new CreditException(
                'Total pembayaran melebihi total pesanan (Rp '.number_format($total, 0, ',', '.').').'
            );
        }

        return round($amount, 2);
    }

    private function appendNotes(CreditApplication $app, ?string $notes): void
    {
        $notes = trim((string) $notes);
        if ($notes === '') {
            return;
        }

        $app->notes = $app->notes
            ? $app->notes."\n".$notes
            : $notes;
    }

    /**
     * Selesaikan order setelah unit diambil (mengikuti status lifecycle order).
     */
    private function completeOrder(?Order $order): void
    {
        // Reload: relasi $app->order bisa basi bila status order diubah
        // oleh pihak lain (mis. buyer konfirmasi terima / admin proses order)
        // setelah relasi sempat di-cache pada model.
        $order = $order?->fresh();

        if (! $order || $order->status === 'completed' || $order->status === 'cancelled') {
            return;
        }

        if ($order->status === 'shipped') {
            $this->orderService->markAsCompleted($order);

            return;
        }

        // Ambil di dealer: unit langsung diserahkan ke customer.
        if ($order->status === 'processing' && $order->isDealerPickup()) {
            $this->orderService->markAsShipped($order, 'dealer_pickup', 'Unit diambil customer');
            $this->orderService->markAsCompleted($order);
        }
    }

    private function newApplicationNo(): string
    {
        $base = 'KRT'.now()->format('ymd');

        do {
            $no = $base.Str::upper(Str::random(5));
        } while (CreditApplication::query()->where('application_no', $no)->exists());

        return $no;
    }
}
