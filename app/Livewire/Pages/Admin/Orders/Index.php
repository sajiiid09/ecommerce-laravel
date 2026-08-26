<?php

namespace App\Livewire\Pages\Admin\Orders;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Index extends Component
{
    use WithPagination;

    public string $search = '';

    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function render()
    {
        $orders = Order::query()
            ->with('user')
            ->withCount('items')
            ->when($this->search, fn ($query) => $query->where(function ($query): void {
                $query->where('order_number', 'like', '%'.$this->search.'%')
                    ->orWhere('customer_email', 'like', '%'.$this->search.'%')
                    ->orWhere('customer_name', 'like', '%'.$this->search.'%');
            }))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->latest('placed_at')
            ->paginate(15);

        return view('livewire.pages.admin.orders.index', compact('orders'));
    }
}
