<?php

namespace App\Livewire\Pages\Admin\Content\Redirects;

use App\Livewire\Concerns\WithAdminTable;
use App\Models\Redirect;
use App\Services\RedirectService;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class Index extends Component
{
    use WithAdminTable;
    use WithFileUploads;

    public $file;

    public string $search = '';

    public string $from_path = '';

    public string $to_url = '';

    public int $status_code = 301;

    public bool $enabled = true;

    protected RedirectService $redirects;

    public function boot(RedirectService $redirects): void
    {
        $this->redirects = $redirects;
    }

    public function saveRedirect(): void
    {
        $this->authorize('create', Redirect::class);
        $data = $this->validate([
            'from_path' => ['required', 'string', 'max:2048'],
            'to_url' => ['required', 'string', 'max:2048'],
            'status_code' => ['required', 'in:301,302,307,308'],
            'enabled' => ['boolean'],
        ]);

        try {
            $this->redirects->save($data);
        } catch (\InvalidArgumentException $exception) {
            $this->addError('to_url', $exception->getMessage());

            return;
        }

        $this->reset(['from_path', 'to_url']);
        session()->flash('status', 'Redirect saved.');
    }

    public function importRedirects(): void
    {
        $this->authorize('create', Redirect::class);
        $this->validate(['file' => ['required', 'file', 'mimes:csv,txt', 'max:5120']]);

        try {
            $count = $this->redirects->import($this->file);
            $this->reset('file');
            session()->flash('status', $count.' redirects imported.');
        } catch (\InvalidArgumentException $exception) {
            $this->addError('file', $exception->getMessage());
        }
    }

    public function deleteRedirect(int $id): void
    {
        $redirect = Redirect::findOrFail($id);
        $this->authorize('delete', $redirect);
        $redirect->delete();
    }

    protected function rows()
    {
        return Redirect::query()
            ->when($this->search, fn ($query) => $query->where(fn ($query) => $query
                ->where('from_path', 'like', '%'.$this->search.'%')
                ->orWhere('to_url', 'like', '%'.$this->search.'%')))
            ->orderBy($this->sortField, $this->sortDirection);
    }

    public function render()
    {
        $this->authorize('viewAny', Redirect::class);

        return view('livewire.pages.admin.content.redirects.index', [
            'redirects' => $this->rows()->paginate($this->perPage),
            'stats' => [
                'Total redirects' => Redirect::count(),
                'Enabled' => Redirect::where('enabled', true)->count(),
                'Redirect hits' => Redirect::sum('hit_count'),
                'Permanent' => Redirect::where('status_code', 301)->count(),
            ],
        ]);
    }
}
