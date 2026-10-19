<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ItemPartCatalogImage extends Model
{
    protected $fillable = ['item_part_catalog_id', 'path', 'sort_order'];

    public function catalog(): BelongsTo
    {
        return $this->belongsTo(ItemPartCatalog::class, 'item_part_catalog_id');
    }
}
