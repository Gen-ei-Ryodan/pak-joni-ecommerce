<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Part360Image extends Model
{
    use HasFactory;

    protected $table = 'part_360_images';

    protected $fillable = ['part_id', 'image_path', 'sort_order'];

    public function part(): BelongsTo
    {
        return $this->belongsTo(Part::class);
    }
}
