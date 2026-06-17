<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class MapsLocation extends Model
{
    use HasFactory;

    protected $fillable = [
        'name', 'type', 'address', 'latitude', 'longitude',
        'phone', 'whatsapp', 'email', 'description', 'is_active', 'sort_order'
    ];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
