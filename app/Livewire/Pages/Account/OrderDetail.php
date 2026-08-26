<?php

namespace App\Livewire\Pages\Account;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class OrderDetail extends Component
{
    public Order $orderModel;

    public function mount(string $order): void
    {
        $this->orderModel = auth()->user()->orders()->with(['items', 'shippingAddress', 'payment', 'statusHistory'])->where('order_number', $order)->firstOrFail();
    }

    public function render()
    {
        return view('pages.account.order-detail-content');
    }
}
