<?php

namespace App\Services;

use App\Models\Order;
use App\Models\Payment;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PaymentService
{
    /**
     * Configure Midtrans global settings.
     */
    public function configureMidtrans(): void
    {
        $isProduction = config('services.midtrans.is_production', false);

        \Midtrans\Config::$serverKey = config('services.midtrans.server_key');
        \Midtrans\Config::$clientKey = config('services.midtrans.client_key');
        \Midtrans\Config::$isProduction = $isProduction;
        \Midtrans\Config::$isSanitized = config('services.midtrans.is_sanitized', true);
        \Midtrans\Config::$is3ds = config('services.midtrans.is_3ds', true);

        if (! $isProduction) {
            \Midtrans\Config::$overrideNotifUrl = route('payment.midtrans.notification');
        }
    }

    /**
     * Create a payment record for the order.
     */
    public function createPayment(Order $order, string $method = 'midtrans', string $provider = 'midtrans'): Payment
    {
        return Payment::create([
            'order_id' => $order->id,
            'provider' => $provider,
            'provider_reference' => null,
            'status' => 'pending',
            'payload' => ['method' => $method],
        ]);
    }

    /**
     * Get the Midtrans Snap transaction token for an order.
     * Returns the snap token string, or null on failure.
     */
    public function getSnapToken(Order $order): ?string
    {
        try {
            $this->configureMidtrans();

            $customerName = $order->user->name ?? 'Customer';
            $customerEmail = $order->user->email ?? 'customer@example.com';
            $snapData = $order->address_snapshot;
            $customerPhone = $snapData['phone'] ?? '';

            $items = [];
            foreach ($order->items as $it) {
                $items[] = [
                    'id' => $it->sku ?: 'ITEM-'.$it->id,
                    'price' => (int) round((float) $it->price),
                    'quantity' => (int) $it->quantity,
                    'name' => substr($it->name, 0, 50),
                ];
            }

            // Add shipping as a separate item
            $shippingCost = (int) round((float) $order->shipping_cost);
            if ($shippingCost > 0) {
                $items[] = [
                    'id' => 'SHIPPING',
                    'price' => $shippingCost,
                    'quantity' => 1,
                    'name' => 'Shipping Cost',
                ];
            }

            $transactionDetails = [
                'order_id' => $order->id,
                'gross_amount' => (int) round((float) $order->total),
            ];

            $customerDetails = [
                'first_name' => $customerName,
                'email' => $customerEmail,
                'phone' => $customerPhone,
            ];

            $params = [
                'transaction_details' => $transactionDetails,
                'item_details' => $items,
                'customer_details' => $customerDetails,
                'callbacks' => [
                    'finish' => route('buyer.orders.show', $order),
                ],
            ];

            $snapToken = \Midtrans\Snap::getSnapToken($params);

            // Store snap token in payment payload
            $order->payment()->update([
                'provider_reference' => $snapToken,
                'payload' => array_merge($order->payment?->payload ?? [], [
                    'snap_token' => $snapToken,
                    'transaction_details' => $transactionDetails,
                ]),
            ]);

            return $snapToken;
        } catch (\Exception $e) {
            Log::error('Midtrans getSnapToken error: '.$e->getMessage(), [
                'order_id' => $order->id,
                'trace' => $e->getTraceAsString(),
            ]);
            return null;
        }
    }

    /**
     * Process incoming Midtrans notification (server-to-server callback).
     */
    public function processNotification(array $payload): array
    {
        try {
            $this->configureMidtrans();

            $notif = new \Midtrans\Notification();

            $transactionStatus = $notif->transaction_status;
            $fraudStatus = $notif->fraud_status;
            $orderId = $notif->order_id;

            if (! $orderId) {
                return ['success' => false, 'message' => 'No order_id in notification'];
            }

            $order = Order::query()->with('payment')->find($orderId);

            if (! $order) {
                return ['success' => false, 'message' => 'Order not found: '.$orderId];
            }

            // Store raw notification in payment payload
            $order->payment()->update([
                'provider_reference' => $notif->transaction_id ?? $notif->order_id,
                'payload' => array_merge($order->payment?->payload ?? [], [
                    'notification' => $payload,
                    'transaction_status' => $transactionStatus,
                    'fraud_status' => $fraudStatus,
                    'payment_type' => $notif->payment_type,
                    'transaction_time' => $notif->transaction_time,
                    'bank' => $notif->bank,
                    'va_numbers' => $notif->va_numbers,
                ]),
            ]);

            $successStatuses = ['settlement', 'capture'];
            $failedStatuses = ['deny', 'cancel', 'failure'];
            $expiredStatuses = ['expire'];

            if (in_array($transactionStatus, $successStatuses)) {
                if ($transactionStatus === 'capture' && $fraudStatus === 'challenge') {
                    // Transaction is challenged, mark as pending review
                    return ['success' => true, 'message' => 'Payment challenged, pending review'];
                }
                $this->markPaymentSuccess($order, $notif);
            } elseif (in_array($transactionStatus, $failedStatuses)) {
                $this->paymentFailed($order);
            } elseif (in_array($transactionStatus, $expiredStatuses)) {
                $this->paymentExpired($order);
            }

            return ['success' => true, 'message' => 'Notification processed: '.$transactionStatus];
        } catch (\Exception $e) {
            Log::error('Midtrans notification error: '.$e->getMessage(), [
                'payload' => $payload,
                'trace' => $e->getTraceAsString(),
            ]);
            return ['success' => false, 'message' => $e->getMessage()];
        }
    }

    /**
     * Handle Midtrans callback from redirect (user arrives at finish/redirect URL).
     */
    public function midtransCallbackHandler(array $payload): array
    {
        $orderId = $payload['order_id'] ?? null;
        $transactionStatus = $payload['transaction_status'] ?? null;

        if (! $orderId || ! $transactionStatus) {
            return ['success' => false, 'message' => 'Invalid payload'];
        }

        $order = Order::query()->with('payment')->find($orderId);

        if (! $order) {
            return ['success' => false, 'message' => 'Order not found'];
        }

        // Avoid re-processing if already paid
        if ($order->payment_status === 'paid') {
            return ['success' => true, 'message' => 'Already paid'];
        }

        match ($transactionStatus) {
            'settlement', 'capture' => $this->simulateSuccessPayment($order),
            'deny', 'cancel' => $this->paymentFailed($order),
            'expire' => $this->paymentExpired($order),
            default => null,
        };

        return ['success' => true];
    }

    /**
     * Mark order as paid (internal, from midtrans notification).
     */
    private function markPaymentSuccess(Order $order, \Midtrans\Notification $notif): void
    {
        DB::transaction(function () use ($order, $notif) {
            $paymentType = $notif->payment_type ?? 'unknown';
            $transactionId = $notif->transaction_id ?? ('MT-'.$order->order_no.'-'.now()->timestamp);

            $order->update([
                'payment_status' => 'paid',
                'status' => 'paid',
                'paid_at' => now(),
                'payment_method' => $paymentType,
                'payment_provider' => 'midtrans',
                'payment_reference' => $transactionId,
            ]);
        });
    }

    /**
     * Simulate successful payment (for testing/simulated mode).
     */
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

    /**
     * Mark payment as failed.
     */
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

    /**
     * Mark payment as expired.
     */
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
}
