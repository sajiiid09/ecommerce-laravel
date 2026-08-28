<?php

namespace App\Livewire\Pages\Admin\Customers;

use App\Models\User;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Index extends Component
{
    use WithPagination;

    public string $searchQuery = '';

    public int $perPage = 15;

    public ?int $viewingCustomerId = null;

    public function updatedSearchQuery(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function viewCustomer(int $customerId): void
    {
        User::query()
            ->where('is_admin', false)
            ->findOrFail($customerId);

        $this->viewingCustomerId = $customerId;
        $this->resetPage('customerOrdersPage');
        $this->resetPage('customerReviewsPage');
        $this->dispatch('open-modal', id: 'customer-details');
    }

    public function previousCustomerOrdersPage(): void
    {
        $this->previousPage('customerOrdersPage');
    }

    public function nextCustomerOrdersPage(): void
    {
        $this->nextPage('customerOrdersPage');
    }

    public function previousCustomerReviewsPage(): void
    {
        $this->previousPage('customerReviewsPage');
    }

    public function nextCustomerReviewsPage(): void
    {
        $this->nextPage('customerReviewsPage');
    }

    public function render(): View
    {
        $customers = User::query()
            ->where('is_admin', false)
            ->withCount('orders')
            ->when($this->searchQuery !== '', fn ($query) => $query->where(function ($query): void {
                $query->where('name', 'like', '%'.$this->searchQuery.'%')
                    ->orWhere('email', 'like', '%'.$this->searchQuery.'%')
                    ->orWhere('phone', 'like', '%'.$this->searchQuery.'%');
            }))
            ->orderBy('name')
            ->paginate($this->perPage);

        $viewingCustomer = null;
        $customerOrders = null;
        $customerReviews = null;

        if ($this->viewingCustomerId !== null) {
            $viewingCustomer = User::query()
                ->where('is_admin', false)
                ->withCount(['orders', 'reviews'])
                ->withSum('orders', 'total_minor')
                ->find($this->viewingCustomerId);

            if ($viewingCustomer) {
                $customerOrders = $viewingCustomer->orders()
                    ->latest('placed_at')
                    ->paginate(5, ['*'], 'customerOrdersPage');
                $customerReviews = $viewingCustomer->reviews()
                    ->with('product')
                    ->latest()
                    ->paginate(5, ['*'], 'customerReviewsPage');
            }
        }

        return view('livewire.pages.admin.customers.index', compact(
            'customers',
            'viewingCustomer',
            'customerOrders',
            'customerReviews',
        ));
    }
}
