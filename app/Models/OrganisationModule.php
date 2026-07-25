<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

class OrganisationModule extends Model
{
    use BelongsToTenant;
    use SoftDeletes;

    protected $fillable = [
        'organisation_id',
        'marketplace_module_id',
        'is_enabled',
        'installed_at',
        'settings',
    ];

    protected function casts(): array
    {
        return [
            'is_enabled' => 'boolean',
            'installed_at' => 'datetime',
            'settings' => 'array',
        ];
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function marketplaceModule(): BelongsTo
    {
        return $this->belongsTo(MarketplaceModule::class);
    }
}
