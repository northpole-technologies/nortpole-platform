<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

class MarketplaceModule extends Model
{
    use SoftDeletes;

    protected $fillable = [
        'key',
        'name',
        'description',
        'version',
        'category',
        'icon',
        'is_core',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_core' => 'boolean',
            'is_active' => 'boolean',
        ];
    }
}
