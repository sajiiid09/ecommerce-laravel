<?php

namespace App\Livewire\Pages\Admin;

use App\Enums\ProductStatus;
use App\Models\InventoryItem;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductVariant;
use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Dashboard extends Component
{
    public function render(): View
    {
        $stats = [
            'products' => Product::query()->count(),
            'published' => Product::query()->where('status', ProductStatus::Published->value)->count(),
            'low_stock' => InventoryItem::query()->whereColumn('quantity_on_hand', '<=', 'low_stock_threshold')->count(),
            'variants' => ProductVariant::query()->count(),
            'orders' => Order::query()->count(),
            'customers' => User::query()->where('is_admin', false)->count(),
            'sales' => (int) Order::query()->where('status', '!=', 'cancelled')->sum('total_minor'),
        ];

        return view('livewire.pages.admin.dashboard', [
            'stats' => $stats,
            'recentOrders' => Order::query()->latest('placed_at')->take(5)->get(),
            'lowStock' => InventoryItem::query()
                ->with(['variant.product'])
                ->where('track_quantity', true)
                ->whereColumn('quantity_on_hand', '<=', 'low_stock_threshold')
                ->orderBy('quantity_on_hand')
                ->take(5)
                ->get(),
        ]);
    }
}
