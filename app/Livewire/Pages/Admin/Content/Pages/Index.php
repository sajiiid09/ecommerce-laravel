<?php

namespace App\Livewire\Pages\Admin\Content\Pages;

use App\Livewire\Concerns\WithAdminTable;
use App\Models\Page;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Index extends Component
{
    use WithAdminTable;

    public string $search = '';

    public string $status = '';

    public function updatedSearch(): void
    {
        $this->resetPage();
    }

    public function deletePage(int $id): void
    {
        $page = Page::findOrFail($id);
        $this->authorize('delete', $page);
        $page->delete();
    }

    protected function rows()
    {
        return Page::query()
            ->when($this->search, fn ($query) => $query->where(fn ($query) => $query
                ->where('title', 'like', '%'.$this->search.'%')
                ->orWhere('slug', 'like', '%'.$this->search.'%')))
            ->when($this->status, fn ($query) => $query->where('status', $this->status))
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function render()
    {
        $this->authorize('viewAny', Page::class);

        return view('livewire.pages.admin.content.pages.index', [
            'pages' => $this->rows()->paginate($this->perPage),
            'pageStats' => [
                'total' => Page::count(),
                'published' => Page::published()->count(),
                'draft' => Page::where('status', 'draft')->count(),
                'scheduled' => Page::where('status', 'scheduled')->count(),
            ],
        ]);
    }
}
