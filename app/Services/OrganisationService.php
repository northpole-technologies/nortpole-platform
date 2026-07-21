<?php

namespace App\Services;

use App\Models\Organisation;
use Illuminate\Support\Str;

class OrganisationService
{
    public function create(array $data): Organisation
    {
        return Organisation::create([
            'id'        => Str::ulid(),
            'name'      => $data['name'],
            'slug'      => $data['slug'],
            'email'     => $data['email'] ?? null,
            'phone'     => $data['phone'] ?? null,
            'website'   => $data['website'] ?? null,
            'country'   => $data['country'] ?? null,
            'timezone'  => $data['timezone'] ?? 'Europe/Dublin',
            'active'    => true,
        ]);
    }
}