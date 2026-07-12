<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Career extends Model
{
    use HasFactory;

    protected $table = 'careers';

    protected $fillable = [
        'title', 'slug', 'location', 'description', 'requirements',
        'thumbnail_path', 'publish_date',
        'display_start_date', 'display_end_date',
        'status', 'is_active',
    ];

    protected function casts(): array
    {
        return [
            'publish_date' => 'datetime',
            'display_start_date' => 'date',
            'display_end_date' => 'date',
            'is_active' => 'boolean',
        ];
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }
}
