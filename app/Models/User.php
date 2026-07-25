<?php

namespace App\Models;

use App\Support\Tenancy\TenantContext;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;
    use HasFactory;
    use Notifiable;

    protected $fillable = [
        'name',
        'email',
        'password',
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function organisations(): BelongsToMany
    {
        return $this->belongsToMany(Organisation::class)
            ->withPivot([
                'role',
                'role_id',
                'is_active',
                'joined_at',
            ])
            ->withTimestamps();
    }

    public function activeOrganisations(): BelongsToMany
    {
        return $this->organisations()
            ->wherePivot('is_active', true);
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
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

    public function belongsToOrganisation(
        Organisation|string $organisation
    ): bool {
        $organisationId = $organisation instanceof Organisation
            ? $organisation->getKey()
            : $organisation;

        return $this->activeOrganisations()
            ->whereKey($organisationId)
            ->exists();
    }

    public function roleForOrganisation(
        Organisation|string $organisation
    ): ?Role {
        $organisationId = $organisation instanceof Organisation
            ? $organisation->getKey()
            : $organisation;

        return app(TenantContext::class)->withoutTenancy(
            fn (): ?Role => $this->roles()
                ->wherePivot(
                    'organisation_id',
                    $organisationId
                )
                ->wherePivot('is_active', true)
                ->where('roles.is_active', true)
                ->first()
        );
    }

    public function hasPermission(
        Permission|string $permission,
        Organisation|string|null $organisation = null
    ): bool {
        $organisation ??= app(TenantContext::class)
            ->organisation();

        $role = $this->roleForOrganisation($organisation);

        if ($role === null) {
            return false;
        }

        return $role->hasPermission($permission);
    }
}
