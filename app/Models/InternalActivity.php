<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class InternalActivity extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'thumbnail_path', 'content', 'publish_date', 'is_active'];

    protected function casts(): array
    {
        return [
            'publish_date' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(InternalActivityGallery::class)->orderBy('sort_order');
    }
}
