<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PriceList extends Model
{
    use HasFactory;

    protected $fillable = ['motor_id', 'name', 'pdf_path', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function motor(): BelongsTo
    {
        return $this->belongsTo(Motor::class);
    }
}
