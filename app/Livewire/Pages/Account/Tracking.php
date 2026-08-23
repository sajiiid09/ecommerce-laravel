<?php

namespace App\Livewire\Pages\Account;

use App\Support\StorefrontDemoData;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Tracking extends Component
{
    public ?string $order = null;

    public function mount(?string $order = null): void
    {
        $this->order = $order;
    }

    public function render()
    {
        $order = collect(StorefrontDemoData::orders())->firstWhere('id', $this->order)
            ?? StorefrontDemoData::orders()[0];

        return view('pages.account.tracking-content', [
            'orderData' => $order,
            'timeline' => StorefrontDemoData::trackingTimeline(),
        ]);
    }
}
