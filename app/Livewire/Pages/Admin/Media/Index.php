<?php

namespace App\Livewire\Pages\Admin\Media;

use App\Enums\ImagePreset;
use App\Livewire\Concerns\WithAdminTable;
use App\Models\MediaAsset;
use App\Models\MediaFolder;
use App\Services\MediaService;
use Illuminate\Support\Facades\Log;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithFileUploads;

#[Layout('layouts.admin')]
class Index extends Component
{
    use WithAdminTable, WithFileUploads;

    public $file;

    public string $search = '';

    public string $type = '';

    public ?int $folderId = null;

    public ?int $selectedAssetId = null;

    public string $alt_text = '';

    public string $title = '';

    public string $caption = '';

    public string $folderName = '';

    protected MediaService $media;

    public function boot(MediaService $media): void
    {
        $this->media = $media;
    }

    public function updatedFile(): void
    {
        $this->validate(['file' => 'required|image|max:10240']);
        $this->authorize('create', MediaAsset::class);
        $this->media->upload($this->file, 'general', ImagePreset::General);
        $this->reset('file');
        session()->flash('status', 'Media uploaded successfully.');
    }

    public function deleteAsset(int $id): void
    {
        try {
            $asset = MediaAsset::findOrFail($id);
            $this->authorize('delete', $asset);
            $this->media->delete($asset);
            $this->selectedAssetId = null;
            session()->flash('status', 'Media asset moved to the recycle bin.');
        } catch (\Throwable $exception) {
            Log::warning('Media deletion blocked', ['asset_id' => $id, 'message' => $exception->getMessage()]);
            $this->addError('delete', $exception->getMessage());
        }
    }

    public function updatedSelectedAssetId(?int $id): void
    {
        $asset = $id ? MediaAsset::find($id) : null;
        $this->alt_text = $asset?->alt_text ?? '';
        $this->title = $asset?->title ?? '';
        $this->caption = $asset?->caption ?? '';
    }

    public function saveMetadata(): void
    {
        $asset = MediaAsset::findOrFail($this->selectedAssetId);
        $this->authorize('update', $asset);
        $this->media->updateMetadata($asset, ['alt_text' => $this->alt_text, 'title' => $this->title, 'caption' => $this->caption]);
        session()->flash('status', 'Media metadata saved.');
    }

    public function createFolder(): void
    {
        $this->validate(['folderName' => 'required|string|max:80']);
        $this->authorize('create', MediaAsset::class);
        $this->media->createFolder($this->folderName);
        $this->reset('folderName');
        session()->flash('status', 'Media folder created.');
    }

    public function render()
    {
        $this->authorize('viewAny', MediaAsset::class);
        $assets = MediaAsset::query()->withCount('usages')->when($this->search, fn ($q) => $q->where(fn ($q) => $q->where('filename', 'like', '%'.$this->search.'%')->orWhere('title', 'like', '%'.$this->search.'%')->orWhere('alt_text', 'like', '%'.$this->search.'%')))->when($this->folderId, fn ($q) => $q->where('folder_id', $this->folderId))->when($this->type, fn ($q) => $q->where('mime_type', 'like', $this->type.'/%'))->orderBy($this->sortField, $this->sortDirection)->paginate(24);

        return view('livewire.pages.admin.media.index', [
            'assets' => $assets,
            'folders' => MediaFolder::withCount('assets')->ordered()->get(),
            'selectedAsset' => $this->selectedAssetId ? MediaAsset::withCount('usages')->find($this->selectedAssetId) : null,
            'stats' => [
                'Total Assets' => MediaAsset::count(),
                'Images' => MediaAsset::where('mime_type', 'like', 'image/%')->count(),
                'Storage Used' => number_format(MediaAsset::sum('size') / 1048576, 1).' MB',
                'Recently Uploaded' => MediaAsset::where('created_at', '>=', now()->subDays(7))->count(),
            ],
        ]);
    }
}
