<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

#[Fillable(['motor_id', 'path', 'sort_order'])]
class MotorImage extends Model
{
    use HasFactory;

    protected $fillable = ['motor_id', 'path', 'sort_order'];

    public function motor(): BelongsTo
    {
        return $this->belongsTo(Motor::class);
    }
}
