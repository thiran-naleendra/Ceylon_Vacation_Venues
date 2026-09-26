<?php

namespace App\Policies;

use App\Models\Property;
use App\Models\User;

class PropertyPolicy
{
    public function viewAny(User $u): bool
    {
        return $u->role->canPublishContent();
    }

    public function view(User $u, Property $p): bool
    {
        return $u->role->canPublishContent();
    }

    public function create(User $u): bool
    {
        return $u->role->canPublishContent();
    }

    public function update(User $u, Property $p): bool
    {
        return $u->role->canPublishContent();
    }

    public function delete(User $u, Property $p): bool
    {
        return in_array($u->role->value, ['owner', 'administrator'], true);
    }

    public function publish(User $u, Property $p): bool
    {
        return $u->role->canPublishContent();
    }

    public function feature(User $u, Property $p): bool
    {
        return $u->role->canPublishContent();
    }
}
