<?php

namespace App\Services;

use App\Models\ItemColor;
use App\Models\Order;
use App\Models\PartVariant;
use Illuminate\Support\Facades\DB;

class OrderService
{
    public function __construct(
        protected StockService $stockService
    ) {}

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

    public function cancelOrder(Order $order): bool
    {
        if (! $order->canTransitionTo('cancelled')) {
            return false;
        }

        $order->update([
            'status' => 'cancelled',
            'payment_status' => $order->payment_status === 'pending' ? 'expired' : $order->payment_status,
            'cancelled_at' => now(),
        ]);

        return true;
    }

    public function markAsPaid(Order $order): bool
    {
        $result = $this->updateStatus($order, 'paid', [
            'payment_status' => 'paid',
            'paid_at' => now(),
        ]);

        if ($result) {
            $this->decreaseStockOnOrder($order);
        }

        return $result;
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

    protected function decreaseStockOnOrder(Order $order): void
    {
        foreach ($order->items as $orderItem) {
            if ($orderItem->itemable_type === ItemColor::class) {
                $color = ItemColor::find($orderItem->itemable_id);
                if ($color) {
                    try {
                        $this->stockService->decreaseStockOnOrder($color, $orderItem->quantity, $order->id);
                    } catch (\InvalidArgumentException $e) {
                        report($e);
                    }
                }
            } elseif ($orderItem->itemable_type === PartVariant::class) {
                $variant = PartVariant::find($orderItem->itemable_id);
                if ($variant) {
                    try {
                        $this->stockService->decreaseStockOnOrder($variant, $orderItem->quantity, $order->id);
                    } catch (\InvalidArgumentException $e) {
                        report($e);
                    }
                }
            }
        }
    }
}
