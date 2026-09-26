<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;

class VehiclePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->canPublishContent();
    }

    public function view(User $user, Vehicle $vehicle): bool
    {
        return $user->role->canPublishContent();
    }

    public function create(User $user): bool
    {
        return $user->role->canPublishContent();
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        return $user->role->canPublishContent();
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        return in_array($user->role->value, ['owner', 'administrator'], true);
    }

    public function publish(User $user, Vehicle $vehicle): bool
    {
        return $user->role->canPublishContent();
    }

    public function feature(User $user, Vehicle $vehicle): bool
    {
        return $user->role->canPublishContent();
    }

    public function restore(User $user, Vehicle $vehicle): bool
    {
        return false;
    }

    public function forceDelete(User $user, Vehicle $vehicle): bool
    {
        return false;
    }
}
