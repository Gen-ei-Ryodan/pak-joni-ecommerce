<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class News extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'thumbnail_path', 'content', 'author', 'category', 'publish_date', 'is_active'];

    protected function casts(): array
    {
        return [
            'publish_date' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
