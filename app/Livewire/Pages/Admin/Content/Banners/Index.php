<?php

namespace App\Livewire\Pages\Admin\Content\Banners;

use App\Livewire\Concerns\WithAdminTable;
use App\Models\Banner;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Index extends Component
{
    use WithAdminTable;

    public string $search = '';

    public string $statusFilter = '';

    public string $placementFilter = '';

    public function mount(): void
    {
        $this->placementFilter = (string) request()->query('placementFilter', '');
    }

    public function deleteBanner(int $id): void
    {
        $banner = Banner::findOrFail($id);
        $this->authorize('delete', $banner);
        $banner->delete();
    }

    protected function rows()
    {
        return Banner::query()->with(['desktopMedia'])
            ->when($this->search, fn ($query) => $query->where('name', 'like', '%'.$this->search.'%'))
            ->when($this->statusFilter, fn ($query) => $query->where('status', $this->statusFilter))
            ->when($this->placementFilter, fn ($query) => $query->where('placement', $this->placementFilter))
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function render()
    {
        $this->authorize('viewAny', Banner::class);

        return view('livewire.pages.admin.content.banners.index', [
            'banners' => $this->rows()->paginate($this->perPage),
            'stats' => [
                'Total banners' => Banner::count(),
                'Published' => Banner::where('status', 'published')->count(),
                'Scheduled' => Banner::where('status', 'scheduled')->count(),
                'Active placements' => Banner::whereIn('status', ['published', 'scheduled'])->distinct('placement')->count('placement'),
            ],
        ]);
    }
}
