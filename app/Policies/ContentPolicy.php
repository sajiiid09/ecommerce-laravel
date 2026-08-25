<?php

namespace App\Policies;

use App\Models\User;

class ContentPolicy
{
    public function before(User $user): ?bool
    {
        return $user->is_admin ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function view(User $user, mixed $model): bool
    {
        return $user->is_admin;
    }

    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, mixed $model): bool
    {
        return $user->is_admin;
    }

    public function delete(User $user, mixed $model): bool
    {
        return $user->is_admin;
    }

    public function publish(User $user, mixed $model): bool
    {
        return $user->is_admin;
    }

    public function restore(User $user, mixed $model): bool
    {
        return $user->is_admin;
    }

    public function export(User $user): bool
    {
        return $user->is_admin;
    }
}
