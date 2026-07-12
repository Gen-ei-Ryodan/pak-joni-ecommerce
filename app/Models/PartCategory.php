<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['category_type_id', 'group', 'name', 'slug', 'sort_order'])]
class PartCategory extends Model
{
    use HasFactory;

    protected $fillable = ['category_type_id', 'group', 'name', 'slug', 'sort_order'];

    public function type(): BelongsTo
    {
        return $this->belongsTo(CategoryType::class, 'category_type_id');
    }

    public function parts(): HasMany
    {
        return $this->hasMany(Part::class)->orderBy('name');
    }
}
