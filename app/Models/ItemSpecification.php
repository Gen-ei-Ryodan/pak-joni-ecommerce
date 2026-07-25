<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemSpecification extends Model
{
    use HasFactory;

    protected $fillable = ['item_id', 'group', 'key', 'value', 'sort_order', 'mforce_id', 'is_active'];

    protected function casts(): array
    {
        return ['is_active' => 'boolean'];
    }

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
