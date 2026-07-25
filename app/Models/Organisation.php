<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Concerns\HasUlids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

class Organisation extends Model
{
    use HasUlids;
    use SoftDeletes;

    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'name',
        'slug',
        'email',
        'phone',
        'website',
        'country',
        'timezone',
        'active',
    ];

    protected function casts(): array
    {
        return [
            'active' => 'boolean',
        ];
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(User::class)
            ->withPivot([
                'role',
                'role_id',
                'is_active',
                'joined_at',
            ])
            ->withTimestamps();
    }

    public function activeUsers(): BelongsToMany
    {
        return $this->users()
            ->wherePivot('is_active', true);
    }

    public function modules(): HasMany
    {
        return $this->hasMany(OrganisationModule::class);
    }

    public function roles(): HasMany
    {
        return $this->hasMany(Role::class);
    }
}
