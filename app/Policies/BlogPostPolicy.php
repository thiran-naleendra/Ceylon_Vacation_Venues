<?php

namespace App\Policies;

use App\Models\BlogPost;
use App\Models\User;

class BlogPostPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->canPublishContent();
    }

    public function view(User $user, BlogPost $record): bool
    {
        return $user->role->canPublishContent();
    }

    public function create(User $user): bool
    {
        return $user->role->canPublishContent();
    }

    public function update(User $user, BlogPost $record): bool
    {
        return $user->role->canPublishContent();
    }

    public function publish(User $user, BlogPost $record): bool
    {
        return $user->role->canPublishContent();
    }

    public function delete(User $user, BlogPost $record): bool
    {
        return in_array($user->role->value, ['owner', 'administrator'], true);
    }
}
