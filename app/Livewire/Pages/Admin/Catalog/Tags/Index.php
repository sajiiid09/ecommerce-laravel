<?php

namespace App\Livewire\Pages\Admin\Catalog\Tags;

use App\Livewire\Pages\Admin\Catalog\ResourceIndex;
use App\Models\Tag;

class Index extends ResourceIndex
{
    protected function model(): string
    {
        return Tag::class;
    }

    protected function title(): string
    {
        return 'Tags';
    }

    public function bulk(string $action): void
    {
        $this->validate(['selected' => ['array']]);

        foreach (Tag::whereKey($this->selected)->get() as $tag) {
            $this->authorize($action === 'delete' ? 'delete' : 'update', $tag);

            if ($action === 'delete') {
                $tag->delete();
            } elseif ($action === 'activate' || $action === 'deactivate') {
                $tag->update(['is_active' => $action === 'activate']);
            }
        }

        $this->clearSelection();
        $this->resetPage();
    }
}
