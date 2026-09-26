<?php

namespace App\Policies;

use App\Models\User;
use App\Models\VehicleCategory;

class VehicleCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->canPublishContent();
    }

    public function view(User $user, VehicleCategory $category): bool
    {
        return $user->role->canPublishContent();
    }

    public function create(User $user): bool
    {
        return $user->role->canPublishContent();
    }

    public function update(User $user, VehicleCategory $category): bool
    {
        return $user->role->canPublishContent();
    }

    public function delete(User $user, VehicleCategory $category): bool
    {
        return in_array($user->role->value, ['owner', 'administrator'], true);
    }

    public function restore(User $user, VehicleCategory $category): bool
    {
        return false;
    }

    public function forceDelete(User $user, VehicleCategory $category): bool
    {
        return false;
    }
}
