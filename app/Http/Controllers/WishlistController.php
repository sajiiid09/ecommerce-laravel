<?php

namespace App\Http\Controllers;

use App\Services\WishlistService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class WishlistController extends Controller
{
    public function add(int $productId, WishlistService $wishlists): JsonResponse
    {
        return response()->json(['ids' => $wishlists->add($productId)]);
    }

    public function remove(int $productId, WishlistService $wishlists): JsonResponse
    {
        return response()->json(['ids' => $wishlists->remove($productId)]);
    }

    public function clear(WishlistService $wishlists): JsonResponse
    {
        return response()->json(['ids' => $wishlists->clear()]);
    }

    public function sync(Request $request, WishlistService $wishlists): JsonResponse
    {
        $data = $request->validate([
            'ids' => ['array', 'max:100'],
            'ids.*' => ['integer', 'min:1'],
        ]);

        return response()->json(['ids' => $wishlists->syncGuest($data['ids'] ?? [])]);
    }
}
