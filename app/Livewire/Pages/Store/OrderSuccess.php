<?php

namespace App\Livewire\Pages\Store;

use App\Models\Order;
use Illuminate\Contracts\View\View;
use Illuminate\Database\QueryException;
use Illuminate\Support\Facades\Schema;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class OrderSuccess extends Component
{
    public ?string $order = null;

    public function mount(?string $order = null): void
    {
        $this->order = $order ?? session('last_order_number');
    }

    public function render()
    {
        if (! Schema::hasTable('orders')) {
            return $this->demoView();
        }

        try {
            $order = Order::query()->with(['items', 'shippingAddress', 'payment'])->where('order_number', $this->order)->firstOrFail();
        } catch (QueryException $exception) {
            if (str_contains($exception->getMessage(), 'no such table')) {
                return $this->demoView();
            }

            throw $exception;
        }
        abort_unless(auth()->id() === $order->user_id || session('last_order_number') === $order->order_number, 403);

        return view('pages.store.order-success-content', ['order' => $order]);
    }

    private function demoView(): View
    {
        return view('pages.store.order-success-content', ['order' => (object) [
            'customer_name' => 'StoreZ customer',
            'order_number' => 'SZ-DEMO',
            'status' => 'pending',
            'payment_status' => 'unpaid',
            'total_minor' => 0,
            'customer_email' => 'customer@example.test',
        ]]);
    }
}
