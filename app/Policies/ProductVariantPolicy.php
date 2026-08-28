<?php

namespace App\Policies;

use App\Models\ProductVariant;
use App\Models\User;

class ProductVariantPolicy
{
    public function before(User $user): ?bool
    {
        return $user->is_admin ? true : null;
    }

    public function viewAny(User $user): bool
    {
        return $user->is_admin;
    }

    public function view(User $user, ProductVariant $variant): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, ProductVariant $variant): bool
    {
        return $user->is_admin;
    }

    public function delete(User $user, ProductVariant $variant): bool
    {
        return $user->is_admin;
    }
}
