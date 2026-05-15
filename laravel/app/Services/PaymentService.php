<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;

class PaymentService
{
    public function createPayment(Order $order, string $method = 'simulated', string $provider = 'simulated'): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'provider' => $provider,
            'provider_reference' => null,
            'status' => 'pending',
            'payload' => ['method' => $method],
        ]);
    }

    public function simulateSuccessPayment(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->update([
                'payment_status' => 'paid',
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => 'simulated',
                'payment_provider' => 'simulated',
            ]);

            $order->payment()->updateOrCreate(
                ['order_id' => $order->id],
                [
                    'provider' => 'simulated',
                    'provider_reference' => 'SIM-'.$order->order_no.'-'.now()->timestamp,
                    'status' => 'success',
                    'payload' => ['simulated' => true, 'paid_at' => now()->toDateTimeString()],
                ]
            );
        });
    }

    public function paymentFailed(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->update([
                'payment_status' => 'failed',
            ]);

            $order->payment()->updateOrCreate(
                ['order_id' => $order->id],
                ['status' => 'failed']
            );
        });
    }

    public function paymentExpired(Order $order): void
    {
        DB::transaction(function () use ($order) {
            $order->update([
                'payment_status' => 'expired',
            ]);

            $order->payment()->updateOrCreate(
                ['order_id' => $order->id],
                ['status' => 'expired']
            );
        });
    }

    public function midtransCallbackHandler(array $payload): array
    {
        // Placeholder: nanti tinggal implementasi sesuai callback Midtrans
        // Tidak mengubah flow besar aplikasi
        $orderId = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;

        if (! $orderId || ! $transactionStatus) {
            return ['success' => false, 'message' => 'Invalid payload'];
        }

        $order = Order::query()->with('payment')->find($orderId);

        if (! $order) {
            return ['success' => false, 'message' => 'Order not found'];
        }

        match ($transactionStatus) {
            'settlement', 'capture' => $this->simulateSuccessPayment($order),
            'deny', 'cancel' => $this->paymentFailed($order),
            'expire' => $this->paymentExpired($order),
            default => null,
        };

        return ['success' => true];
    }
}
