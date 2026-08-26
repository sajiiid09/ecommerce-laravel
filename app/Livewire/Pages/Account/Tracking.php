<?php

namespace App\Livewire\Pages\Account;

use App\Models\Order;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Tracking extends Component
{
    public ?string $order = null;

    public Order $orderModel;

    public function mount(?string $order = null): void
    {
        $this->order = $order;
        $query = auth()->user()->orders()->with(['statusHistory' => fn ($history) => $history->oldest('created_at')]);
        $this->orderModel = $query
            ->when($order, fn ($orders) => $orders->where('order_number', $order))
            ->latest('placed_at')
            ->firstOrFail();
    }

    public function render()
    {
        $statuses = ['pending', 'processing', 'completed'];
        $currentIndex = array_search($this->orderModel->status, $statuses, true);
        $currentIndex = $currentIndex === false ? 0 : $currentIndex;
        $historyByStatus = $this->orderModel->statusHistory->keyBy('to_status');
        $timeline = collect($statuses)->map(function (string $status, int $index) use ($currentIndex, $historyByStatus): array {
            $history = $historyByStatus->get($status);

            return [
                'label' => str($status)->replace('_', ' ')->title()->toString(),
                'active' => $index === $currentIndex && $this->orderModel->status !== 'completed',
                'completed' => $index < $currentIndex || $this->orderModel->status === 'completed',
                'date' => $history?->created_at?->format('M j, Y g:i A'),
            ];
        })->all();

        return view('pages.account.tracking-content', [
            'orderData' => $this->orderModel,
            'timeline' => $timeline,
        ]);
    }
}
