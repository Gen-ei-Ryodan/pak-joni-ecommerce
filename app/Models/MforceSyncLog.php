<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class MforceSyncLog extends Model
{
    protected $fillable = [
        'sync_type', 'brand_slug', 'trigger',
        'started_at', 'finished_at', 'duration_ms',
        'created', 'updated', 'skipped', 'archived', 'errors',
        'error_details', 'status',
    ];

    protected function casts(): array
    {
        return [
            'started_at' => 'datetime',
            'finished_at' => 'datetime',
            'created' => 'integer',
            'updated' => 'integer',
            'skipped' => 'integer',
            'archived' => 'integer',
            'errors' => 'integer',
        ];
    }
}
