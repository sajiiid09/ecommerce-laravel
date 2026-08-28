<?php

namespace App\Policies;

use App\Models\ProductReview;
use App\Models\User;

class ProductReviewPolicy
{
    public function view(User $user, ProductReview $review): bool
    {
        return $user->is_admin;
    }

    public function update(User $user, ProductReview $review): bool
    {
        return $user->is_admin;
    }

    public function delete(User $user, ProductReview $review): bool
    {
        return $user->is_admin;
    }
}
