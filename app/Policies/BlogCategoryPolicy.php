<?php

namespace App\Policies;

use App\Models\BlogCategory;
use App\Models\User;

class BlogCategoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->canPublishContent();
    }

    public function view(User $user, BlogCategory $record): bool
    {
        return $user->role->canPublishContent();
    }

    public function create(User $user): bool
    {
        return $user->role->canPublishContent();
    }

    public function update(User $user, BlogCategory $record): bool
    {
        return $user->role->canPublishContent();
    }

    public function publish(User $user, BlogCategory $record): bool
    {
        return $user->role->canPublishContent();
    }

    public function delete(User $user, BlogCategory $record): bool
    {
        return in_array($user->role->value, ['owner', 'administrator'], true);
    }
}
