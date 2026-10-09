<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Voucher extends Model
{
    use HasFactory;

    protected $fillable = [
        'code',
        'name',
        'discount_type',
        'discount_value',
        'min_spend',
        'max_discount',
        'quota',
        'used_count',
        'is_active',
        'start_date',
        'end_date',
    ];

    protected $casts = [
        'discount_type' => 'string',
        'min_spend' => 'decimal:2',
        'discount_value' => 'decimal:2',
        'max_discount' => 'decimal:2',
        'quota' => 'integer',
        'used_count' => 'integer',
        'is_active' => 'boolean',
        'start_date' => 'date',
        'end_date' => 'date',
    ];

    public function isValidForOrder($orderSubtotal): bool
    {
        // Check if active
        if (! $this->is_active) {
            return false;
        }

        // Check date range
        $now = now();
        if ($now->lt($this->start_date) || $now->gt($this->end_date)) {
            return false;
        }

        // Check quota
        if ($this->quota > 0 && $this->used_count >= $this->quota) {
            return false;
        }

        // Check min_spend
        if ($this->min_spend > 0 && $orderSubtotal < $this->min_spend) {
            return false;
        }

        return true;
    }

    public function calculateDiscount($subtotal): float
    {
        $discount = 0.0;

        if ($this->discount_type === 'percent') {
            $discount = $subtotal * $this->discount_value / 100;
            if ($this->max_discount > 0) {
                $discount = min($discount, $this->max_discount);
            }
        } elseif ($this->discount_type === 'fixed') {
            $discount = $this->discount_value;
            if ($this->max_discount > 0) {
                $discount = min($discount, $this->max_discount);
            }
        }

        return $discount;
    }

    public function canApplyTo($orderSubtotal): bool
    {
        if (! $this->is_active) {
            return false;
        }

        $now = now();
        if ($now->lt($this->start_date) || $now->gt($this->end_date)) {
            return false;
        }

        if ($this->quota > 0 && $this->used_count >= $this->quota) {
            return false;
        }

        if ($this->min_spend > 0 && $orderSubtotal < $this->min_spend) {
            return false;
        }

        return true;
    }
}