<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'image_path', 'link_url', 'is_active', 'sort_order'])]
class Banner extends Model
{
    use HasFactory;

        protected $fillable = ['title', 'image_path', 'link_url', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
