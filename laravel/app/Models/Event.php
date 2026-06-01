<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Event extends Model
{
    use HasFactory;

    protected $table = 'events';

    protected $fillable = ['title', 'slug', 'thumbnail_path', 'description', 'content', 'location', 'event_date', 'is_active'];

    protected function casts(): array
    {
        return [
            'event_date' => 'datetime',
            'is_active' => 'boolean',
        ];
    }

    public function galleries(): HasMany
    {
        return $this->hasMany(EventGallery::class)->orderBy('sort_order');
    }
}
