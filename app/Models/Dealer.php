<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Dealer extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'address', 'city', 'province', 'phone', 'whatsapp', 'email', 'google_maps_url', 'is_active', 'sort_order'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }
}
