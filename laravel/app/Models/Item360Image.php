<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Item360Image extends Model
{
    use HasFactory;

    protected $table = 'item_360_images';

    protected $fillable = ['item_id', 'path', 'sort_order'];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
}
