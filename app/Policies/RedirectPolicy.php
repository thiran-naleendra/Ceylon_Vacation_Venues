<?php

namespace App\Policies;

use App\Models\Redirect;
use App\Models\User;

class RedirectPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->role->canPublishContent();
    }

    public function create(User $user): bool
    {
        return in_array($user->role->value, ['owner', 'administrator'], true);
    }

    public function update(User $user, Redirect $redirect): bool
    {
        return in_array($user->role->value, ['owner', 'administrator'], true);
    }

    public function delete(User $user, Redirect $redirect): bool
    {
        return in_array($user->role->value, ['owner', 'administrator'], true);
    }
}
