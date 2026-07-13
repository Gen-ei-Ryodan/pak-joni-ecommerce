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
                $price = (int) round((float) $it->price);
                $qty = (int) $it->quantity;
                $name = substr($it->name, 0, 50);
                $sku = $it->sku ?: 'ITEM-'.$it->id;

                $items[] = [
                    'id' => $sku,
                    'price' => $price,
                    'quantity' => $qty,
                    'name' => $name,
                ];
            }

            // For indent orders: add discount (remaining amount) so item totals match gross_amount
            if ($order->is_indent && (float) $order->remaining_amount > 0) {
                $items[] = [
                    'id' => 'DISCOUNT',
                    'price' => -(int) round((float) $order->remaining_amount),
                    'quantity' => 1,
                    'name' => 'Diskon DP 50%',
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

            $grossAmount = (int) round((float) $order->total);

            // Security: recalculate total from items to prevent manipulation
            $calculatedTotal = 0;
            foreach ($items as $item) {
                $calculatedTotal += $item['price'] * $item['quantity'];
            }
            if ($calculatedTotal !== $grossAmount) {
                Log::warning('Order total mismatch in getSnapToken', [
                    'order_id' => $order->id,
                    'order_total' => $grossAmount,
                    'calculated_total' => $calculatedTotal,
                ]);
            }

            $transactionDetails = [
                'order_id' => $order->order_no,
                'gross_amount' => $grossAmount,
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

            // Verify webhook signature
            if (! $this->verifyWebhookSignature($payload)) {
                Log::warning('Midtrans webhook signature verification failed', ['payload' => $payload]);
                return ['success' => false, 'message' => 'Invalid signature'];
            }

            // Idempotency check: prevent processing the same notification twice
            $idempotencyKey = ($payload['transaction_id'] ?? '')
                . '-' . ($payload['transaction_status'] ?? '')
                . '-' . ($payload['status_code'] ?? '');

            $notif = new \Midtrans\Notification();

            $transactionStatus = $notif->transaction_status;
            $fraudStatus = $notif->fraud_status;
            $orderId = $notif->order_id;

            if (! $orderId) {
                return ['success' => false, 'message' => 'No order_id in notification'];
            }

            $order = Order::query()->with('payment')->where('order_no', $orderId)->first();

            if (! $order) {
                return ['success' => false, 'message' => 'Order not found: '.$orderId];
            }

            // Check idempotency: skip if this exact notification was already processed
            $prevIdempotency = $order->payment?->payload['idempotency_key'] ?? null;
            if ($prevIdempotency === $idempotencyKey) {
                Log::info('Midtrans duplicate notification skipped', [
                    'order_no' => $orderId,
                    'idempotency_key' => $idempotencyKey,
                ]);
                return ['success' => true, 'message' => 'Duplicate notification skipped'];
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
                    'idempotency_key' => $idempotencyKey,
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

        $order = Order::query()->with('payment')->where('order_no', $orderId)->first();

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

            // Return reserved stock
            $this->returnStock($order);
        });
    }

    /**
     * Check payment status directly from Midtrans API.
     * Returns array with 'paid' and 'transaction_status' keys.
     */
    public function checkStatusFromMidtrans(Order $order): array
    {
        $this->configureMidtrans();

        $url = \Midtrans\Config::$isProduction
            ? 'https://api.midtrans.com/v2/' . $order->order_no . '/status'
            : 'https://api.sandbox.midtrans.com/v2/' . $order->order_no . '/status';

        $serverKey = config('services.midtrans.server_key');

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json',
            'Authorization: Basic ' . base64_encode($serverKey . ':'),
        ]);

        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);

        if ($httpCode !== 200) {
            Log::warning('Midtrans status check failed', [
                'order_no' => $order->order_no,
                'http_code' => $httpCode,
                'response' => $response,
            ]);
            return ['paid' => false, 'transaction_status' => 'pending'];
        }

        $data = json_decode($response, true);

        if (! $data) {
            return ['paid' => false, 'transaction_status' => 'pending'];
        }

        $transactionStatus = $data['transaction_status'] ?? 'pending';
        $paid = in_array($transactionStatus, ['settlement', 'capture']);

        // If Midtrans says paid but local DB not updated yet, sync it
        if ($paid && $order->payment_status !== 'paid') {
            DB::transaction(function () use ($order, $data) {
                $order->update([
                    'payment_status' => 'paid',
                    'status' => 'paid',
                    'paid_at' => $data['settlement_time'] ?? now(),
                    'payment_method' => $data['payment_type'] ?? 'unknown',
                    'payment_provider' => 'midtrans',
                    'payment_reference' => $data['transaction_id'] ?? null,
                ]);

                $order->payment()->updateOrCreate(
                    ['order_id' => $order->id],
                    [
                        'provider' => 'midtrans',
                        'provider_reference' => $data['transaction_id'] ?? null,
                        'status' => 'success',
                        'payload' => array_merge($order->payment?->payload ?? [], [
                            'status_check' => $data,
                        ]),
                    ]
                );
            });
        }

        return [
            'paid' => $paid,
            'transaction_status' => $transactionStatus,
        ];
    }

    /**
     * Verify Midtrans webhook signature to prevent spoofed notifications.
     */
    private function verifyWebhookSignature(array $payload): bool
    {
        $serverKey = config('services.midtrans.server_key');
        $orderId = $payload['order_id'] ?? '';
        $statusCode = (string) ($payload['status_code'] ?? '');
        $grossAmount = (string) ($payload['gross_amount'] ?? '');
        $signatureKey = $payload['signature_key'] ?? '';

        // Midtrans signature format: SHA512(order_id + status_code + gross_amount + server_key)
        $computedSignature = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if (! hash_equals($computedSignature, (string) $signatureKey)) {
            Log::warning('Midtrans webhook signature mismatch', [
                'order_id' => $orderId,
                'computed' => $computedSignature,
                'received' => $signatureKey,
            ]);
            return false;
        }

        return true;
    }

    /**
     * Return reserved stock when payment expires.
     */
    private function returnStock(Order $order): void
    {
        foreach ($order->items as $item) {
            if ($item->itemable_type === 'App\\Models\\PartVariant') {
                $variant = \App\Models\PartVariant::lockForUpdate()->find($item->itemable_id);
                if ($variant) {
                    $readyQty = max(0, $item->quantity - (int)($item->indent_quantity ?? 0));
                    if ($readyQty > 0) {
                        $variant->stock += $readyQty;
                        $variant->stock_updated_at = now();
                        $variant->save();
                    }
                }
            }
        }
    }
}
