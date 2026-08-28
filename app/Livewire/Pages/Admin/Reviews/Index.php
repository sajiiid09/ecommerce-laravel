<?php

namespace App\Livewire\Pages\Admin\Reviews;

use App\Models\ProductReview;
use App\Services\ReviewService;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Index extends Component
{
    use WithPagination;

    public string $status = 'all';

    public int $perPage = 15;

    public ?ProductReview $viewingReview = null;

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function view(int $reviewId): void
    {
        $review = ProductReview::with('product')->findOrFail($reviewId);
        Gate::authorize('view', $review);

        $this->viewingReview = $review;
        $this->dispatch('open-modal', id: 'review-details');
    }

    public function approve(int $reviewId, ReviewService $reviews): void
    {
        $review = ProductReview::findOrFail($reviewId);
        Gate::authorize('update', $review);
        $reviews->approve($review);
    }

    public function reject(int $reviewId, ReviewService $reviews): void
    {
        $review = ProductReview::findOrFail($reviewId);
        Gate::authorize('update', $review);
        $reviews->reject($review);
    }

    public function delete(int $reviewId, ReviewService $reviews): void
    {
        $review = ProductReview::findOrFail($reviewId);
        Gate::authorize('delete', $review);
        $reviews->delete($review);
    }

    public function render()
    {
        return view('livewire.pages.admin.reviews.index', ['reviews' => ProductReview::with('product')->when($this->status !== 'all', fn ($query) => $query->where('status', $this->status))->latest()->paginate($this->perPage)]);
    }
}
