<?php

namespace App\Livewire\Pages\Store;

use App\Services\CatalogQueryService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Brand extends Component
{
    public ?string $slug = null;

    protected CatalogQueryService $catalog;

    public function boot(CatalogQueryService $catalog): void
    {
        $this->catalog = $catalog;
    }

    public function mount(string $slug): void
    {
        $this->slug = $slug;
    }

    public function render()
    {
        $brand = $this->catalog->brand($this->slug);

        return view('pages.store.brand-content', [
            'slug' => $this->slug,
            'brandName' => $brand?->name ?? strtoupper($this->slug),
            'products' => $this->catalog->products(['brands' => [$this->slug]]),
        ]);
    }
}
