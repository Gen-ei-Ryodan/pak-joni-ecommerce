<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class StoreAddress extends Model
{
    use HasFactory;

    protected $fillable = [
        'label',
        'address_line1',
        'address_line2',
        'city',
        'district',
        'province',
        'postal_code',
        'is_default',
    ];

    protected function casts(): array
    {
        return [
            'is_default' => 'boolean',
        ];
    }

    public static function boot()
    {
        parent::boot();

        // Ensure only one address is default at a time
        static::saving(function (self $address) {
            if ($address->is_default) {
                static::where('id', '!=', $address->id)
                    ->where('is_default', true)
                    ->update(['is_default' => false]);
            }
        });
    }

    public static function default(): ?self
    {
        return static::where('is_default', true)->first();
    }
}
