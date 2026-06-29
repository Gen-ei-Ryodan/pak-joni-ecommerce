<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function updateStatus(Order $order, string $newStatus, array $extra = []): bool
    {
        if (! $order->canTransitionTo($newStatus)) {
            return false;
        }

        return DB::transaction(function () use ($order, $newStatus, $extra) {
            $data = ['status' => $newStatus];

            match ($newStatus) {
                'paid' => $data['paid_at'] = $data['paid_at'] ?? now(),
                'shipped' => $data['shipped_at'] = now(),
                'completed' => $data['completed_at'] = now(),
                'cancelled' => $data['cancelled_at'] = now(),
                default => null,
            };

            if (! empty($extra)) {
                $data = array_merge($data, $extra);
            }

            $order->update($data);

            return true;
        });
    }

    public function cancelOrder(Order $order, ?string $reason = null): bool
    {
        if (! $order->canTransitionTo('cancelled')) {
            return false;
        }

        return DB::transaction(function () use ($order, $reason) {
            $order->load('items');

            $order->update([
                'status' => 'cancelled',
                'payment_status' => $order->payment_status === 'pending' ? 'expired' : $order->payment_status,
                'cancelled_at' => now(),
                'cancellation_reason' => $reason,
            ]);

            // Return reserved stock for unpaid orders
            if ($order->payment_status === 'pending') {
                $this->returnStock($order);
            }

            return true;
        });
    }

    /**
     * Return reserved stock when order is cancelled/expired before payment.
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

    public function markAsPaid(Order $order): bool
    {
        return $this->updateStatus($order, 'paid', [
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);
    }

    public function processOrder(Order $order): bool
    {
        return $this->updateStatus($order, 'processing');
    }

    public function markAsShipped(Order $order, string $courier, string $receipt): bool
    {
        return $this->updateStatus($order, 'shipped', [
            'shipping_courier' => $courier,
            'shipping_receipt' => $receipt,
        ]);
    }

    public function markAsCompleted(Order $order): bool
    {
        return $this->updateStatus($order, 'completed');
    }
}
