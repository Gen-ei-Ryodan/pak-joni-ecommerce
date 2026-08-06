<?php

namespace App\Services;

use App\Models\Item;
use App\Models\ItemColor;
use App\Models\PartVariant;
use App\Models\StockMutation;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class StockService
{
    public function adjustStock(
        Model $stockable,
        int $quantity,
        string $type = 'manual',
        ?string $notes = null,
        ?string $referenceType = null,
        ?int $referenceId = null
    ): StockMutation {
        return DB::transaction(function () use ($stockable, $quantity, $type, $notes, $referenceType, $referenceId) {
            $previousStock = $this->getCurrentStock($stockable);
            $newStock = $previousStock + $quantity;

            if ($newStock < 0) {
                throw new \InvalidArgumentException('Stock cannot be negative');
            }

            $this->updateStock($stockable, $newStock);

            $mutation = StockMutation::create([
                'stockable_type' => get_class($stockable),
                'stockable_id' => $stockable->id,
                'quantity' => $quantity,
                'previous_stock' => $previousStock,
                'current_stock' => $newStock,
                'type' => $type,
                'reference_type' => $referenceType,
                'reference_id' => $referenceId,
                'notes' => $notes,
                'user_id' => Auth::id(),
            ]);

            return $mutation;
        });
    }

    public function setStock(
        Model $stockable,
        int $newStock,
        ?string $notes = null
    ): StockMutation {
        $previousStock = $this->getCurrentStock($stockable);
        $quantity = $newStock - $previousStock;

        return $this->adjustStock($stockable, $quantity, 'manual', $notes);
    }

    public function getCurrentStock(Model $stockable): int
    {
        if ($stockable instanceof Item) {
            return (int) $stockable->stock;
        }

        if ($stockable instanceof ItemColor) {
            return (int) $stockable->stock;
        }

        if ($stockable instanceof PartVariant) {
            return (int) $stockable->stock;
        }

        throw new \InvalidArgumentException('Unsupported stockable type');
    }

    protected function updateStock(Model $stockable, int $newStock): void
    {
        if ($stockable instanceof Item) {
            $stockable->update([
                'stock' => $newStock,
                'stock_updated_at' => now(),
            ]);
        } elseif ($stockable instanceof ItemColor) {
            $stockable->update([
                'stock' => $newStock,
                'stock_updated_at' => now(),
            ]);
        } elseif ($stockable instanceof PartVariant) {
            $stockable->update([
                'stock' => $newStock,
                'stock_updated_at' => now(),
            ]);
        }
    }

    public function getMutationHistory(Model $stockable, int $limit = 50): \Illuminate\Database\Eloquent\Collection
    {
        return $stockable->stockMutations()
            ->with('user')
            ->orderByDesc('created_at')
            ->limit($limit)
            ->get();
    }

    public function decreaseStockOnOrder(Model $stockable, int $quantity, int $orderId): StockMutation
    {
        return $this->adjustStock(
            $stockable,
            -$quantity,
            'order',
            'Order #' . $orderId,
            'order',
            $orderId
        );
    }
}
