<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Motor360Image extends Model
{
    use HasFactory;

    protected $table = 'motor_360_images';

    protected $fillable = ['motor_id', 'path', 'sort_order'];

    public function motor(): BelongsTo
    {
        return $this->belongsTo(Motor::class);
    }
}
