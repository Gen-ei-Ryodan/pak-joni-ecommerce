<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CsrArticle extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'slug', 'thumbnail_path', 'content', 'documentation', 'publish_date', 'is_active'];

    protected function casts(): array
    {
        return [
            'documentation' => 'array',
            'publish_date' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
