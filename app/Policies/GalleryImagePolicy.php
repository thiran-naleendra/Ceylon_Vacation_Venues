<?php

namespace App\Policies;

use App\Models\GalleryImage;
use App\Models\User;

class GalleryImagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->canPublishContent();
    }

    public function view(User $user, GalleryImage $record): bool
    {
        return $user->role->canPublishContent();
    }

    public function create(User $user): bool
    {
        return $user->role->canPublishContent();
    }

    public function update(User $user, GalleryImage $record): bool
    {
        return $user->role->canPublishContent();
    }

    public function publish(User $user, GalleryImage $record): bool
    {
        return $user->role->canPublishContent();
    }

    public function delete(User $user, GalleryImage $record): bool
    {
        return in_array($user->role->value, ['owner', 'administrator'], true);
    }
}
