<?php

namespace App\Policies;

use App\Models\GalleryAlbum;
use App\Models\User;

class GalleryAlbumPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->canPublishContent();
    }

    public function view(User $user, GalleryAlbum $record): bool
    {
        return $user->role->canPublishContent();
    }

    public function create(User $user): bool
    {
        return $user->role->canPublishContent();
    }

    public function update(User $user, GalleryAlbum $record): bool
    {
        return $user->role->canPublishContent();
    }

    public function publish(User $user, GalleryAlbum $record): bool
    {
        return $user->role->canPublishContent();
    }

    public function delete(User $user, GalleryAlbum $record): bool
    {
        return in_array($user->role->value, ['owner', 'administrator'], true);
    }
}
