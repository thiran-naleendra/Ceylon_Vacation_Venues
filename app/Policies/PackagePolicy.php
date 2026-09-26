<?php

namespace App\Policies;

use App\Models\TourPackage;
use App\Models\User;

class PackagePolicy
{
    /**
     * Determine whether the user can view any models.
     */
    public function viewAny(User $user): bool
    {
        return $user->role->canPublishContent();
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, TourPackage $tourPackage): bool
    {
        return $user->role->canPublishContent();
    }

    /**
     * Determine whether the user can create models.
     */
    public function create(User $user): bool
    {
        return $user->role->canPublishContent();
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, TourPackage $tourPackage): bool
    {
        return $user->role->canPublishContent();
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, TourPackage $tourPackage): bool
    {
        return in_array($user->role->value, ['owner', 'administrator'], true);
    }

    /**
     * Determine whether the user can restore the model.
     */
    public function restore(User $user, TourPackage $tourPackage): bool
    {
        return false;
    }

    /**
     * Determine whether the user can permanently delete the model.
     */
    public function forceDelete(User $user, TourPackage $tourPackage): bool
    {
        return false;
    }

    public function publish(User $user, TourPackage $tourPackage): bool
    {
        return $user->role->canPublishContent();
    }

    public function feature(User $user, TourPackage $tourPackage): bool
    {
        return $user->role->canPublishContent();
    }
}
