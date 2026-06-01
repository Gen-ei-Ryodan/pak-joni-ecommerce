<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MotorSpecification extends Model
{
    use HasFactory;

    protected $fillable = ['motor_id', 'group', 'key', 'value', 'sort_order'];

    public function motor(): BelongsTo
    {
        return $this->belongsTo(Motor::class);
    }
}
