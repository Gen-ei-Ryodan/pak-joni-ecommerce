<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MotorColor extends Model
{
    use HasFactory;

    protected $fillable = ['motor_id', 'name', 'color_code', 'image_path', 'sort_order'];

    public function motor(): BelongsTo
    {
        return $this->belongsTo(Motor::class);
    }
}
