<?php

namespace App\Livewire\Pages\Admin\Orders;

use App\Models\Order;
use App\Services\OrderService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Show extends Component
{
    public Order $orderModel;

    public function mount(string $order): void
    {
        $record = Order::query()->where('order_number', $order)->firstOrFail();
        Gate::authorize('view', $record);
        $this->orderModel = $record->load(['user', 'items', 'shippingAddress', 'payment', 'statusHistory.changedBy']);
    }

    public function changeStatus(string $status, OrderService $orders): void
    {
        Gate::authorize('update', $this->orderModel);
        $this->orderModel = $orders->transition($this->orderModel, $status);
        session()->flash('status', 'Order status updated.');
    }

    public function render()
    {
        return view('livewire.pages.admin.orders.show');
    }
}
