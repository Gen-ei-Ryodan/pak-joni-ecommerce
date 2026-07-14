<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class HeroVideo extends Model
{
    protected $fillable = ['title', 'video_path', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
