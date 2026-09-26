<?php

namespace App\Policies;

use App\Models\PropertyType;
use App\Models\User;

class PropertyTypePolicy
{
    public function viewAny(User $u): bool
    {
        return $u->role->canPublishContent();
    }

    public function create(User $u): bool
    {
        return $u->role->canPublishContent();
    }

    public function update(User $u, PropertyType $p): bool
    {
        return $u->role->canPublishContent();
    }

    public function delete(User $u, PropertyType $p): bool
    {
        return in_array($u->role->value, ['owner', 'administrator'], true);
    }
}
