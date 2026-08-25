<?php

namespace App\Policies;

use App\Models\MediaAsset;
use App\Models\User;

class MediaAssetPolicy
{
    public function before(User $user): ?bool
    {
        return $user->is_admin ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function view(User $user, MediaAsset $asset): bool
    {
        return $user->is_admin;
    }

    public function create(User $user): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, MediaAsset $asset): bool
    {
        return $user->is_admin;
    }

    public function delete(User $user, MediaAsset $asset): bool
    {
        return $user->is_admin;
    }

    public function purge(User $user, MediaAsset $asset): bool
    {
        return $user->is_admin;
    }
}
