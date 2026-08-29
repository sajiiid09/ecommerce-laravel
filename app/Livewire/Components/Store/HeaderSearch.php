<?php

namespace App\Livewire\Components\Store;

use App\Services\CatalogQueryService;
use Illuminate\Contracts\View\View;
use Livewire\Component;

class HeaderSearch extends Component
{
    public array $categories = [];

    public string $mode = 'desktop';

    public string $query = '';

    public function mount(array $categories = [], string $mode = 'desktop'): void
    {
        $this->categories = $categories;
        $this->mode = $mode;
    }

    public function render(): View
    {
        $query = trim($this->query);
        $suggestions = app(CatalogQueryService::class)->productSuggestions($query);

        return view('livewire.components.store.header-search', compact('query', 'suggestions'));
    }
}
