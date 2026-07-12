<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class CategoryType extends Model
{
    use HasFactory;

    protected $fillable = ['name', 'slug', 'description', 'icon', 'sort_order', 'is_active'];

    protected function casts(): array
    {
        return [
            'is_active' => 'boolean',
        ];
    }

    public function categories(): HasMany
    {
        return $this->hasMany(Category::class, 'category_type_id')->orderBy('sort_order');
    }

    public function items(): HasMany
    {
        return $this->hasMany(Item::class, 'category_type_id')->orderBy('sort_order');
    }

    public function parts(): HasMany
    {
        return $this->hasMany(Part::class, 'category_type_id');
    }

    public function partCategories(): HasMany
    {
        return $this->hasMany(PartCategory::class, 'category_type_id');
    }
}
