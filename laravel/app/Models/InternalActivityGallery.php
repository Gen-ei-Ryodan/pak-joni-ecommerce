<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class InternalActivityGallery extends Model
{
    use HasFactory;

    protected $fillable = ['internal_activity_id', 'path', 'sort_order'];

    public function activity()
    {
        return $this->belongsTo(InternalActivity::class, 'internal_activity_id');
    }
}
