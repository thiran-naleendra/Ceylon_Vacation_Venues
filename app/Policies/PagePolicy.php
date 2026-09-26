<?php

namespace App\Policies;

use App\Models\Page;
use App\Models\User;

class PagePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->canPublishContent();
    }

    public function view(User $user, Page $page): bool
    {
        return $user->role->canPublishContent();
    }

    public function update(User $user, Page $page): bool
    {
        return $user->role->canPublishContent();
    }

    public function publish(User $user, Page $page): bool
    {
        return $user->role->canPublishContent();
    }

    public function create(User $user): bool
    {
        return false;
    }

    public function delete(User $user, Page $page): bool
    {
        return false;
    }
}
