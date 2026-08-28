<?php

namespace App\Services;

use App\Enums\CouponDiscountType;
use App\Models\Cart;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class CouponService
{
    /**
     * @return array{coupon: Coupon, code: string, subtotal_minor: int, eligible_subtotal_minor: int, discount_minor: int}
     */
    public function quote(?string $code, Cart $cart, ?User $user = null, ?string $email = null, bool $lock = false): ?array
    {
        if (blank($code)) {
            return null;
        }

        $normalizedCode = $this->normalizeCode($code);
        $coupon = Coupon::query()->where('code', $normalizedCode)->first();

        if (! $coupon) {
            throw ValidationException::withMessages(['coupon_code' => 'This coupon code is invalid.']);
        }

        if ($lock) {
            $coupon = Coupon::query()->whereKey($coupon->id)->lockForUpdate()->firstOrFail();
        }

        $coupon->loadMissing(['products:id', 'categories:id']);
        $cart->loadMissing('items.variant.product.categories', 'items.variant.product.primaryCategory');

        $subtotal = $this->subtotal($cart);
        $this->assertAvailable($coupon, $subtotal, $user, $email);
        $eligibleSubtotal = $this->eligibleSubtotal($coupon, $cart);

        if ($eligibleSubtotal < 1) {
            throw ValidationException::withMessages(['coupon_code' => 'This coupon does not apply to the items in your cart.']);
        }

        $discount = $coupon->discount_type === CouponDiscountType::Percentage
            ? intdiv($eligibleSubtotal * (int) $coupon->percentage, 100)
            : min($eligibleSubtotal, (int) $coupon->amount_minor);

        return [
            'coupon' => $coupon,
            'code' => $normalizedCode,
            'subtotal_minor' => $subtotal,
            'eligible_subtotal_minor' => $eligibleSubtotal,
            'discount_minor' => min($subtotal, max(0, $discount)),
        ];
    }

    public function redeem(?string $code, Cart $cart, ?User $user = null, ?string $email = null): ?array
    {
        return $this->quote($code, $cart, $user, $email, lock: true);
    }

    public function normalizeCode(string $code): string
    {
        return Str::upper(trim($code));
    }

    public function generateCode(): string
    {
        do {
            $code = 'STOREZ-'.Str::upper(Str::random(8));
        } while (Coupon::withTrashed()->where('code', $code)->exists());

        return $code;
    }

    public function status(Coupon $coupon): string
    {
        if (! $coupon->is_active) {
            return 'inactive';
        }

        if ($coupon->starts_at?->isFuture()) {
            return 'scheduled';
        }

        if ($coupon->ends_at?->isPast()) {
            return 'expired';
        }

        return 'active';
    }

    private function assertAvailable(Coupon $coupon, int $subtotal, ?User $user, ?string $email): void
    {
        if (! $coupon->is_active) {
            throw ValidationException::withMessages(['coupon_code' => 'This coupon is inactive.']);
        }

        if ($coupon->starts_at?->isFuture()) {
            throw ValidationException::withMessages(['coupon_code' => 'This coupon is not available yet.']);
        }

        if ($coupon->ends_at?->isPast()) {
            throw ValidationException::withMessages(['coupon_code' => 'This coupon has expired.']);
        }

        if ($subtotal < (int) $coupon->minimum_subtotal_minor) {
            throw ValidationException::withMessages(['coupon_code' => 'Your cart does not meet this coupon’s minimum subtotal.']);
        }

        if ($coupon->usage_limit !== null && $this->usageCount($coupon) >= $coupon->usage_limit) {
            throw ValidationException::withMessages(['coupon_code' => 'This coupon has reached its usage limit.']);
        }

        if ($coupon->per_customer_limit !== null && ($user || filled($email))) {
            $query = Order::query()->where('coupon_id', $coupon->id)->where('status', '!=', 'cancelled');
            if ($user) {
                $query->where('user_id', $user->id);
            } else {
                $query->whereRaw('LOWER(customer_email) = ?', [Str::lower(trim((string) $email))]);
            }

            if ($query->count() >= $coupon->per_customer_limit) {
                throw ValidationException::withMessages(['coupon_code' => 'You have reached this coupon’s usage limit.']);
            }
        }
    }

    private function eligibleSubtotal(Coupon $coupon, Cart $cart): int
    {
        $productIds = $coupon->products->modelKeys();
        $categoryIds = $this->categoryIdsIncludingDescendants($coupon->categories->modelKeys());
        $targeted = $productIds !== [] || $categoryIds !== [];

        return $cart->items->sum(function ($item) use ($productIds, $categoryIds, $targeted): int {
            $product = $item->variant?->product;
            if (! $product) {
                return 0;
            }

            $matches = ! $targeted
                || in_array($product->id, $productIds, true)
                || in_array($product->primary_category_id, $categoryIds, true)
                || $product->categories->pluck('id')->intersect($categoryIds)->isNotEmpty();

            return $matches ? $item->variant->currentPriceMinor() * (int) $item->quantity : 0;
        });
    }

    public function categoryIdsIncludingDescendants(array $categoryIds): array
    {
        $all = array_values(array_unique(array_map('intval', $categoryIds)));
        $frontier = $all;

        while ($frontier !== []) {
            $children = Category::query()->whereIn('parent_id', $frontier)->pluck('id')->all();
            $frontier = array_values(array_diff(array_map('intval', $children), $all));
            $all = array_values(array_unique([...$all, ...$frontier]));
        }

        return $all;
    }

    private function subtotal(Cart $cart): int
    {
        return $cart->items->sum(fn ($item): int => $item->variant->currentPriceMinor() * (int) $item->quantity);
    }

    private function usageCount(Coupon $coupon): int
    {
        return $coupon->orders()->where('status', '!=', 'cancelled')->count();
    }
}
