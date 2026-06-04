<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PartSpecification extends Model
{
    use HasFactory;

    protected $fillable = ['part_id', 'group', 'key', 'value', 'sort_order'];

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }
}
