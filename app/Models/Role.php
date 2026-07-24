<?php

namespace App\Models;

use App\Models\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Role extends Model
{
    use BelongsToTenant;

    protected $fillable = [
        'organisation_id',
        'key',
        'name',
        'description',
        'is_system',
        'is_active',
    ];

    protected function casts(): array
    {
        return [
            'is_system' => 'boolean',
            'is_active' => 'boolean',
        ];
    }

    public function organisation(): BelongsTo
    {
        return $this->belongsTo(Organisation::class);
    }

    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(Permission::class)
            ->withTimestamps();
    }

    public function users(): BelongsToMany
    {
        return $this->belongsToMany(
            User::class,
            'organisation_user'
        )
            ->withPivot([
                'organisation_id',
                'role',
                'is_active',
                'joined_at',
            ])
            ->withTimestamps();
    }

    public function hasPermission(
        Permission|string $permission
    ): bool {
        $permissionKey = $permission instanceof Permission
            ? $permission->key
            : $permission;

        return $this->permissions()
            ->where('permissions.key', $permissionKey)
            ->where('permissions.is_active', true)
            ->exists();
    }
}