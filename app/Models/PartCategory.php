<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['group', 'name', 'slug', 'sort_order'])]
class PartCategory extends Model
{
    use HasFactory;

        protected $fillable = ['group', 'name', 'slug', 'sort_order'];

    public function parts(): HasMany
    {
        return $this->hasMany(Part::class)->orderBy('name');
    }
}
