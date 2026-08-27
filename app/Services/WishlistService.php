<?php

namespace App\Services;

use App\Models\Product;
use App\Models\User;
use App\Models\WishlistItem;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class WishlistService
{
    public const SESSION_KEY = 'wishlist_product_ids';

    /**
     * @return array<int, int>
     */
    public function ids(?User $user = null): array
    {
        $user ??= auth()->user();

        if ($user && Schema::hasTable('wishlist_items')) {
            return WishlistItem::query()
                ->whereBelongsTo($user)
                ->orderBy('id')
                ->pluck('product_id')
                ->map(fn (mixed $id): int => (int) $id)
                ->all();
        }

        return $this->guestIds();
    }

    /**
     * @return array<int, int>
     */
    public function add(int $productId, ?User $user = null): array
    {
        $this->publicProduct($productId);
        $user ??= auth()->user();

        if ($user && Schema::hasTable('wishlist_items')) {
            WishlistItem::query()->firstOrCreate([
                'user_id' => $user->id,
                'product_id' => $productId,
            ]);
        } else {
            $this->storeGuestIds([...$this->guestIds(), $productId]);
        }

        return $this->ids($user);
    }

    /**
     * @return array<int, int>
     */
    public function remove(int $productId, ?User $user = null): array
    {
        $user ??= auth()->user();

        if ($user && Schema::hasTable('wishlist_items')) {
            WishlistItem::query()
                ->whereBelongsTo($user)
                ->where('product_id', $productId)
                ->delete();
        } else {
            $this->storeGuestIds(array_values(array_diff($this->guestIds(), [$productId])));
        }

        return $this->ids($user);
    }

    /**
     * @return array<int, int>
     */
    public function clear(?User $user = null): array
    {
        $user ??= auth()->user();

        if ($user && Schema::hasTable('wishlist_items')) {
            WishlistItem::query()->whereBelongsTo($user)->delete();
        } else {
            session()->forget(self::SESSION_KEY);
        }

        return $this->ids($user);
    }

    /**
     * @param  array<int, mixed>  $ids
     * @return array<int, int>
     */
    public function syncGuest(array $ids): array
    {
        if (auth()->check()) {
            return $this->ids(auth()->user());
        }

        $validIds = $this->validPublicProductIds($ids);
        $this->storeGuestIds($validIds);

        return $validIds;
    }

    /**
     * @return array<int, int>
     */
    public function mergeGuest(User $user): array
    {
        $guestIds = $this->validPublicProductIds($this->guestIds());

        if (Schema::hasTable('wishlist_items') && $guestIds !== []) {
            DB::transaction(function () use ($user, $guestIds): void {
                foreach ($guestIds as $productId) {
                    WishlistItem::query()->firstOrCreate([
                        'user_id' => $user->id,
                        'product_id' => $productId,
                    ]);
                }
            });
        }

        session()->forget(self::SESSION_KEY);

        return $this->ids($user);
    }

    /**
     * @return array<int, int>
     */
    private function guestIds(): array
    {
        return $this->normalizeIds(session()->get(self::SESSION_KEY, []));
    }

    /**
     * @param  array<int, mixed>  $ids
     */
    private function storeGuestIds(array $ids): void
    {
        session()->put(self::SESSION_KEY, $this->validPublicProductIds($ids));
    }

    /**
     * @param  array<int, mixed>  $ids
     * @return array<int, int>
     */
    private function validPublicProductIds(array $ids): array
    {
        $normalizedIds = $this->normalizeIds($ids);

        if ($normalizedIds === [] || ! Schema::hasTable('products')) {
            return $normalizedIds;
        }

        return Product::query()
            ->published()
            ->whereIn('visibility', ['visible', 'catalog_search', 'catalog_only'])
            ->whereIn('id', $normalizedIds)
            ->orderBy('id')
            ->pluck('id')
            ->map(fn (mixed $id): int => (int) $id)
            ->all();
    }

    private function publicProduct(int $productId): Product
    {
        if (! Schema::hasTable('products')) {
            return new Product(['id' => $productId]);
        }

        $product = Product::query()
            ->published()
            ->whereIn('visibility', ['visible', 'catalog_search', 'catalog_only'])
            ->find($productId);

        if (! $product) {
            throw new NotFoundHttpException('The selected product is not available.');
        }

        return $product;
    }

    /**
     * @param  array<int, mixed>  $ids
     * @return array<int, int>
     */
    private function normalizeIds(array $ids): array
    {
        return collect($ids)
            ->map(fn (mixed $id): int => (int) $id)
            ->filter(fn (int $id): bool => $id > 0)
            ->unique()
            ->values()
            ->all();
    }
}
