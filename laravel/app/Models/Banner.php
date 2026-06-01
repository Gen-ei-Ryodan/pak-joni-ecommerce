<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

#[Fillable(['title', 'type', 'subtitle', 'image_path', 'link_url', 'button_text', 'is_active', 'sort_order'])]
class Banner extends Model
{
    use HasFactory;

    protected $fillable = ['title', 'type', 'subtitle', 'image_path', 'link_url', 'button_text', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
