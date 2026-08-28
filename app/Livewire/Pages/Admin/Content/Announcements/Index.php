<?php

namespace App\Livewire\Pages\Admin\Content\Announcements;

use App\Livewire\Concerns\WithAdminTable;
use App\Models\Announcement;
use App\Services\AnnouncementService;
use Closure;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use InvalidArgumentException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Index extends Component
{
    use WithAdminTable;

    public string $search = '';

    public string $placement = 'all';

    public string $status = 'all';

    public ?int $editingId = null;

    public string $internal_title = '';

    public string $message = '';

    public string $style = 'info';

    public string $form_placement = 'top_bar';

    public string $form_status = 'draft';

    public string $priority = 'normal';

    public string $link_label = '';

    public string $link_url = '';

    public string $starts_at = '';

    public string $ends_at = '';

    public bool $dismissible = true;

    protected AnnouncementService $announcements;

    public function boot(AnnouncementService $announcements): void
    {
        $this->announcements = $announcements;
    }

    public function mount(): void
    {
        $this->authorize('viewAny', Announcement::class);
    }

    public function updatedSearch(): void
    {
        $this->resetTablePage();
    }

    public function updatedPlacement(): void
    {
        $this->resetTablePage();
    }

    public function updatedStatus(): void
    {
        $this->resetTablePage();
    }

    public function openCreate(): void
    {
        $this->authorize('create', Announcement::class);
        $this->resetForm();
        $this->dispatch('open-modal', id: 'announcement-editor');
    }

    public function openEdit(int $id): void
    {
        $announcement = Announcement::findOrFail($id);
        $this->authorize('update', $announcement);

        $this->editingId = $announcement->id;
        $this->internal_title = (string) $announcement->internal_title;
        $this->message = (string) $announcement->message;
        $this->style = (string) $announcement->style;
        $this->form_placement = (string) $announcement->placement;
        $this->form_status = (string) $announcement->status;
        $this->priority = (string) $announcement->priority;
        $this->link_label = (string) ($announcement->link_label ?? '');
        $this->link_url = (string) ($announcement->link_url ?? '');
        $this->starts_at = $announcement->starts_at?->format('Y-m-d\TH:i') ?? '';
        $this->ends_at = $announcement->ends_at?->format('Y-m-d\TH:i') ?? '';
        $this->dismissible = (bool) $announcement->dismissible;
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'announcement-editor');
    }

    public function saveAnnouncement(): void
    {
        $announcement = $this->editingId ? Announcement::findOrFail($this->editingId) : null;
        $this->authorize($announcement ? 'update' : 'create', $announcement ?? Announcement::class);

        $validated = $this->validate([
            'internal_title' => ['required', 'string', 'max:255'],
            'message' => ['required', 'string'],
            'style' => ['required', 'in:info,success,warning,danger'],
            'form_placement' => ['required', 'in:top_bar,storefront,checkout,account'],
            'form_status' => ['required', 'in:draft,published,scheduled,archived'],
            'priority' => ['required', 'in:low,normal,high'],
            'dismissible' => ['boolean'],
            'link_label' => ['nullable', 'string', 'max:255'],
            'link_url' => ['nullable', 'string', 'max:2048', $this->validLink()],
            'starts_at' => ['nullable', 'date'],
            'ends_at' => ['nullable', 'date'],
        ]);

        try {
            $this->announcements->save([
                'internal_title' => $validated['internal_title'],
                'message' => $validated['message'],
                'style' => $validated['style'],
                'placement' => $validated['form_placement'],
                'status' => $validated['form_status'],
                'priority' => $validated['priority'],
                'dismissible' => $validated['dismissible'],
                'link_label' => $validated['link_label'],
                'link_url' => $validated['link_url'],
                'starts_at' => $validated['starts_at'] ?: null,
                'ends_at' => $validated['ends_at'] ?: null,
            ], $announcement);
        } catch (InvalidArgumentException $exception) {
            $this->addError('ends_at', $exception->getMessage());

            return;
        }

        $this->dispatch('close-modal', id: 'announcement-editor');
        $this->resetForm();
        session()->flash('status', 'Announcement saved.');
        $this->dispatch('notify', content: 'Announcement saved.', type: 'success');
    }

    public function deleteAnnouncement(int $id): void
    {
        $announcement = Announcement::findOrFail($id);
        $this->authorize('delete', $announcement);
        $this->announcements->delete($announcement);
        session()->flash('status', 'Announcement deleted.');
        $this->dispatch('notify', content: 'Announcement deleted.', type: 'success');
    }

    public function cancelEdit(): void
    {
        $this->resetForm();
        $this->dispatch('close-modal', id: 'announcement-editor');
    }

    protected function rows(): Builder
    {
        return Announcement::query()
            ->when($this->search !== '', fn (Builder $query): Builder => $query->where(fn (Builder $nested): Builder => $nested
                ->where('internal_title', 'like', '%'.$this->search.'%')
                ->orWhere('message', 'like', '%'.$this->search.'%')))
            ->when($this->placement !== 'all', fn (Builder $query): Builder => $query->where('placement', $this->placement))
            ->when($this->status !== 'all', fn (Builder $query): Builder => $query->where('status', $this->status))
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function render(): View
    {
        $this->authorize('viewAny', Announcement::class);

        return view('livewire.pages.admin.content.announcements.index', [
            'announcementRows' => $this->rows()->paginate($this->perPage),
        ]);
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingId',
            'internal_title',
            'message',
            'link_label',
            'link_url',
            'starts_at',
            'ends_at',
        ]);
        $this->style = 'info';
        $this->form_placement = 'top_bar';
        $this->form_status = 'draft';
        $this->priority = 'normal';
        $this->dismissible = true;
        $this->resetValidation();
    }

    private function validLink(): Closure
    {
        return function (string $attribute, mixed $value, Closure $fail): void {
            if (blank($value)) {
                return;
            }

            $linkUrl = trim((string) $value);
            $parsedUrl = parse_url($linkUrl);
            $isRelativePath = str_starts_with($linkUrl, '/') && ! str_starts_with($linkUrl, '//');
            $isHttpUrl = is_array($parsedUrl)
                && in_array($parsedUrl['scheme'] ?? null, ['http', 'https'], true)
                && filled($parsedUrl['host'] ?? null);

            if (! $isRelativePath && ! $isHttpUrl) {
                $fail('The announcement link must be an HTTP(S) URL or a storefront path such as /offers.');
            }
        };
    }
}
