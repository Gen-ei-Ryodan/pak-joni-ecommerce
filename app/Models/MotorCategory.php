<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class MotorCategory extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'sort_order'];

    public function motors(): HasMany
    {
        return $this->hasMany(Motor::class, 'category_id')->orderBy('name');
    }
}
