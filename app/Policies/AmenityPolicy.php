<?php

namespace App\Policies;

use App\Models\Amenity;
use App\Models\User;

class AmenityPolicy
{
    public function viewAny(User $u): bool
    {
        return $u->role->canPublishContent();
    }

    public function create(User $u): bool
    {
        return $u->role->canPublishContent();
    }

    public function update(User $u, Amenity $a): bool
    {
        return $u->role->canPublishContent();
    }

    public function delete(User $u, Amenity $a): bool
    {
        return in_array($u->role->value, ['owner', 'administrator'], true);
    }
}
