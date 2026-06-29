<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\MorphMany;

class MotorColor extends Model
{
    use HasFactory;

    protected $fillable = ['motor_id', 'name', 'color_code', 'image_path', 'weight', 'sort_order'];

    public function motor(): BelongsTo
    {
        return $this->belongsTo(Motor::class);
    }

    public function cartItems(): MorphMany
    {
        return $this->morphMany(CartItem::class, 'itemable');
    }

    public function orderItems(): MorphMany
    {
        return $this->morphMany(OrderItem::class, 'itemable');
    }
}
