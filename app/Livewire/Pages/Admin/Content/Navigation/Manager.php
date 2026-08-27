<?php

namespace App\Livewire\Pages\Admin\Content\Navigation;

use App\Models\Menu;
use App\Models\MenuItem;
use App\Services\MenuService;
use Illuminate\Support\Str;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Manager extends Component
{
    public ?int $menuId = null;

    public ?int $editingItemId = null;

    public string $name = '';

    public string $key = '';

    public string $location = 'header_primary';

    public string $label = '';

    public string $type = 'custom_url';

    public string $url = '';

    public string $route_name = '';

    public ?int $target_id = null;

    public ?int $parent_id = null;

    public bool $enabled = true;

    public int $sort_order = 0;

    protected MenuService $menus;

    public function boot(MenuService $menus): void
    {
        $this->menus = $menus;
    }

    public function mount(): void
    {
        $menu = Menu::firstOrCreate(
            ['key' => 'header-primary'],
            ['name' => 'Header Primary', 'location' => 'header_primary']
        );
        $this->authorize('update', $menu);
        $this->menuId = $menu->id;
        $this->name = $menu->name;
        $this->key = $menu->key;
        $this->location = $menu->location;
    }

    public function saveMenu(): void
    {
        $menu = $this->menu();
        $this->authorize('update', $menu);
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'key' => ['required', 'string', 'max:80'],
            'location' => ['required', 'string', 'max:80'],
        ]);
        $data['key'] = Str::slug($data['key']);
        $this->menus->saveMenu($menu, $data);
        session()->flash('status', 'Navigation menu saved.');
    }

    public function openAddItem(): void
    {
        $this->resetItemForm();
        $this->dispatch('open-modal', id: 'menu-item-editor');
    }

    public function editItem(int $id): void
    {
        $menu = $this->menu();
        $this->authorize('update', $menu);
        $item = MenuItem::where('menu_id', $menu->id)->with('targets')->findOrFail($id);

        $this->editingItemId = $item->id;
        $this->label = $item->label;
        $this->type = $item->type;
        $this->url = $item->url ?? '';
        $this->route_name = $item->route_name ?? '';
        $this->target_id = $item->targets->first()?->target_id;
        $this->parent_id = $item->parent_id;
        $this->enabled = $item->enabled;
        $this->sort_order = $item->sort_order;
        $this->dispatch('open-modal', id: 'menu-item-editor');
    }

    public function addItem(): void
    {
        $this->editingItemId = null;
        $this->saveItem();
    }

    public function saveItem(): void
    {
        $menu = $this->menu();
        $this->authorize('update', $menu);
        $data = $this->validate([
            'label' => ['required', 'string', 'max:255'],
            'type' => ['required', 'in:custom_url,route,page,category,brand,product'],
            'url' => ['nullable', 'url'],
            'route_name' => ['nullable', 'string', 'max:255'],
            'target_id' => ['nullable', 'integer', 'min:1'],
            'parent_id' => ['nullable', 'exists:menu_items,id'],
            'enabled' => ['boolean'],
            'sort_order' => ['integer', 'min:0'],
        ]);

        if (in_array($data['type'], ['page', 'category', 'brand', 'product'], true) && empty($data['target_id'])) {
            $this->addError('target_id', 'An internal target is required.');

            return;
        }

        if ($data['type'] === 'custom_url' && empty($data['url'])) {
            $this->addError('url', 'A custom URL is required.');

            return;
        }

        if (! empty($data['parent_id']) && ! MenuItem::where('menu_id', $menu->id)->whereKey($data['parent_id'])->exists()) {
            $this->addError('parent_id', 'The selected parent does not belong to this menu.');

            return;
        }

        if ($this->editingItemId && (int) $data['parent_id'] === $this->editingItemId) {
            $this->addError('parent_id', 'A menu item cannot be its own parent.');

            return;
        }

        $item = $this->editingItemId
            ? MenuItem::where('menu_id', $menu->id)->findOrFail($this->editingItemId)
            : new MenuItem(['menu_id' => $menu->id]);

        $wasEditing = $this->editingItemId !== null;
        $this->menus->saveItem($item, $data);
        $this->resetItemForm();
        $this->dispatch('close-modal', id: 'menu-item-editor');
        session()->flash('status', $wasEditing ? 'Navigation item updated.' : 'Navigation item added.');
    }

    public function moveItem(int $id, int $direction): void
    {
        $menu = $this->menu();
        $this->authorize('update', $menu);
        $item = MenuItem::where('menu_id', $menu->id)->findOrFail($id);
        $other = MenuItem::where('menu_id', $menu->id)
            ->where('parent_id', $item->parent_id)
            ->where('sort_order', $direction < 0 ? '<' : '>', $item->sort_order)
            ->orderBy('sort_order', $direction < 0 ? 'desc' : 'asc')
            ->first();

        if ($other) {
            [$item->sort_order, $other->sort_order] = [$other->sort_order, $item->sort_order];
            $item->save();
            $other->save();
            $this->menus->invalidate($menu->key);
        }
    }

    public function deleteItem(int $id): void
    {
        $menu = $this->menu();
        $this->authorize('update', $menu);
        $item = MenuItem::where('menu_id', $menu->id)->findOrFail($id);
        $item->delete();
        $this->menus->invalidate($menu->key);
    }

    public function render()
    {
        $menu = Menu::with([
            'items' => fn ($query) => $query->whereNull('parent_id')->with(['children.children', 'children.targets.target', 'targets.target'])->orderBy('sort_order'),
        ])->find($this->menuId);

        return view('livewire.pages.admin.content.navigation.manager', ['menu' => $menu]);
    }

    private function menu(): Menu
    {
        return Menu::findOrFail($this->menuId);
    }

    private function resetItemForm(): void
    {
        $this->editingItemId = null;
        $this->label = '';
        $this->type = 'custom_url';
        $this->url = '';
        $this->route_name = '';
        $this->target_id = null;
        $this->parent_id = null;
        $this->enabled = true;
        $this->sort_order = 0;
        $this->resetValidation();
    }
}
