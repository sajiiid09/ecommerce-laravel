<?php

namespace App\Livewire\Pages\Admin\Content\Announcements;

use App\Livewire\Concerns\WithAdminTable;
use App\Models\Announcement;
use App\Services\AnnouncementService;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Index extends Component
{
    use WithAdminTable;

    public string $search = '';

    public string $internal_title = '';

    public string $message = '';

    public string $style = 'info';

    public string $placement = 'top_bar';

    public string $status = 'draft';

    public string $link_label = '';

    public string $link_url = '';

    public ?string $starts_at = null;

    public ?string $ends_at = null;

    public bool $dismissible = true;

    protected AnnouncementService $announcements;

    public function boot(AnnouncementService $announcements): void
    {
        $this->announcements = $announcements;
    }

    public function saveAnnouncement(): void
    {
        $this->authorize('create', Announcement::class);
        $data = $this->validate([
            'internal_title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'style' => ['required', 'in:info,success,warning,danger'],
            'placement' => ['required', 'string', 'max:80'],
            'status' => ['required', 'in:draft,published,scheduled,archived'],
            'dismissible' => ['boolean'],
            'link_label' => ['nullable', 'string', 'max:255'],
            'link_url' => ['nullable', 'url'],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
        ]);

        try {
            $this->announcements->save($data);
        } catch (\InvalidArgumentException $exception) {
            $this->addError('ends_at', $exception->getMessage());

            return;
        }

        $this->reset(['internal_title', 'message', 'link_label', 'link_url', 'starts_at', 'ends_at']);
        session()->flash('status', 'Announcement saved.');
    }

    public function deleteAnnouncement(int $id): void
    {
        $announcement = Announcement::findOrFail($id);
        $this->authorize('delete', $announcement);
        $announcement->delete();
    }

    protected function rows()
    {
        return Announcement::query()
            ->when($this->search, fn ($query) => $query->where('internal_title', 'like', '%'.$this->search.'%'))
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function render()
    {
        $this->authorize('viewAny', Announcement::class);

        return view('livewire.pages.admin.content.announcements.index', [
            'announcements' => $this->rows()->paginate($this->perPage),
            'stats' => [
                'Total announcements' => Announcement::count(),
                'Published' => Announcement::where('status', 'published')->count(),
                'Scheduled' => Announcement::where('status', 'scheduled')->count(),
                'Dismissible' => Announcement::where('dismissible', true)->count(),
            ],
        ]);
    }
}
