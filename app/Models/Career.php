<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Career extends Model
{
    use HasFactory;

    protected $table = 'careers';

    protected $fillable = ['title', 'location', 'description', 'requirements', 'publish_date', 'status', 'is_active'];

    protected function casts(): array
    {
        return [
            'publish_date' => 'datetime',
            'is_active' => 'boolean',
        ];
    }
}
