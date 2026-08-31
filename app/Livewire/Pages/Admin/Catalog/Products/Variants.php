<?php

namespace App\Livewire\Pages\Admin\Catalog\Products;

use App\Models\MediaAsset;
use App\Models\Product;
use App\Models\ProductOption;
use App\Services\ProductVariantService;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Attributes\On;
use Livewire\Component;

#[Layout('layouts.admin')]
class Variants extends Component
{
    public Product $product;

    public string $optionName = '';

    public string $optionValue = '';

    public ?int $activeOption = null;

    public array $optionValues = [];

    public ?int $selectedVariantId = null;

    public string $variantSku = '';

    public string $variantBarcode = '';

    public string $variantRegularPrice = '0.00';

    public ?string $variantSalePrice = null;

    public ?string $variantCostPrice = null;

    public ?int $variantWeightGrams = null;

    public int $variantQuantity = 0;

    public int $variantLowStockThreshold = 10;

    public bool $variantTrackQuantity = true;

    public bool $variantAllowBackorders = false;

    public bool $variantIsActive = true;

    public bool $variantIsDefault = false;

    public array $selectedVariantMediaIds = [];

    protected ProductVariantService $variants;

    public function boot(ProductVariantService $variants): void
    {
        $this->variants = $variants;
    }

    public function mount(Product $product): void
    {
        $this->product = $product;
        $this->authorize('update', $product);
        $first = $product->variants()->orderByDesc('is_default')->first();
        if ($first) {
            $this->selectVariant($first->id);
        }
    }

    public function addOption(): void
    {
        $this->authorize('update', $this->product);
        $this->optionName = trim($this->optionName);
        $data = $this->validate([
            'optionName' => [
                'required',
                'string',
                'max:100',
            ],
        ]);
        $slug = Str::slug($data['optionName']);

        if ($slug === '') {
            $this->addError('optionName', 'Enter an option name containing letters or numbers.');

            return;
        }

        if ($this->product->options()->where('slug', $slug)->exists()) {
            $this->addError('optionName', 'That option already exists for this product.');

            return;
        }

        $this->product->options()->create(['name' => $data['optionName'], 'slug' => $slug]);
        $this->reset('optionName');
        $this->product->refresh();
    }

    public function addValue(int $optionId): void
    {
        $this->authorize('update', $this->product);
        $value = trim((string) ($this->optionValues[$optionId] ?? ''));
        if ($value === '') {
            $this->addError('optionValues.'.$optionId, 'Enter an option value.');

            return;
        }

        $option = ProductOption::whereBelongsTo($this->product)->findOrFail($optionId);
        $slug = Str::slug($value);

        if ($slug === '') {
            $this->addError('optionValues.'.$optionId, 'Enter an option value containing letters or numbers.');

            return;
        }

        $this->validate([
            'optionValues.'.$optionId => [
                'required',
                'string',
                'max:100',
            ],
        ]);

        if ($option->values()->where('slug', $slug)->exists()) {
            $this->addError('optionValues.'.$optionId, 'That value already exists for this option.');

            return;
        }

        $option->values()->create(['value' => $value, 'slug' => $slug]);
        $this->optionValues[$optionId] = '';
        $this->product->refresh();
    }

    public function removeOption(int $optionId): void
    {
        $this->authorize('update', $this->product);
        ProductOption::whereBelongsTo($this->product)->findOrFail($optionId)->delete();
        $this->variants->generate($this->product);
        $this->product->refresh();
        $this->refreshVariantSelection();
    }

    public function removeValue(int $optionId, int $valueId): void
    {
        $this->authorize('update', $this->product);
        $option = ProductOption::whereBelongsTo($this->product)->findOrFail($optionId);
        $option->values()->findOrFail($valueId)->delete();
        $this->variants->generate($this->product);
        $this->product->refresh();
        $this->refreshVariantSelection();
    }

    public function generate(): void
    {
        $this->authorize('update', $this->product);
        $this->variants->generate($this->product);
        $this->product->refresh();
    }

    public function selectVariant(int $id): void
    {
        $this->authorize('update', $this->product);
        $variant = $this->product->variants()->with('inventory')->findOrFail($id);
        $this->selectedVariantId = $variant->id;
        $this->variantSku = (string) $variant->sku;
        $this->variantBarcode = (string) ($variant->barcode ?? '');
        $this->variantRegularPrice = $this->formatPrice((int) ($variant->regular_price_minor ?? 0));
        $this->variantSalePrice = $this->formatNullablePrice($variant->sale_price_minor);
        $this->variantCostPrice = $this->formatNullablePrice($variant->cost_price_minor);
        $this->variantWeightGrams = $variant->weight_grams;
        $this->variantQuantity = (int) ($variant->inventory?->quantity_on_hand ?? 0);
        $this->variantLowStockThreshold = (int) ($variant->inventory?->low_stock_threshold ?? 10);
        $this->variantTrackQuantity = (bool) ($variant->inventory?->track_quantity ?? true);
        $this->variantAllowBackorders = (bool) ($variant->inventory?->allow_backorders ?? false);
        $this->variantIsActive = (bool) $variant->is_active;
        $this->variantIsDefault = (bool) $variant->is_default;
        $this->selectedVariantMediaIds = $variant->media()
            ->orderBy('sort_order')
            ->pluck('media_asset_id')
            ->filter()
            ->map(fn ($mediaId): int => (int) $mediaId)
            ->all();
    }

    public function saveVariant(): void
    {
        $this->authorize('update', $this->product);
        $variant = $this->product->variants()->findOrFail($this->selectedVariantId);
        $data = $this->validate([
            'variantSku' => ['required', 'string', 'max:100', Rule::unique('product_variants', 'sku')->ignore($variant->id)],
            'variantBarcode' => ['nullable', 'string', 'max:100'],
            'variantRegularPrice' => ['required', 'numeric', 'min:0', 'decimal:0,2'],
            'variantSalePrice' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'variantCostPrice' => ['nullable', 'numeric', 'min:0', 'decimal:0,2'],
            'variantWeightGrams' => ['nullable', 'integer', 'min:0'],
            'variantQuantity' => ['required', 'integer', 'min:0'],
            'variantLowStockThreshold' => ['required', 'integer', 'min:0'],
            'variantTrackQuantity' => ['boolean'],
            'variantAllowBackorders' => ['boolean'],
            'variantIsActive' => ['boolean'],
            'variantIsDefault' => ['boolean'],
            'selectedVariantMediaIds' => ['array'],
            'selectedVariantMediaIds.*' => ['integer', 'exists:media_assets,id'],
        ]);
        $regularPriceMinor = $this->priceToMinor($data['variantRegularPrice']) ?? 0;
        $salePriceMinor = $this->priceToMinor($data['variantSalePrice'] ?? null);
        $costPriceMinor = $this->priceToMinor($data['variantCostPrice'] ?? null);

        if ($salePriceMinor !== null && $salePriceMinor > $regularPriceMinor) {
            $this->addError('variantSalePrice', 'Sale price must not exceed the regular price.');

            return;
        }

        $this->variants->save($variant, [
            'sku' => $data['variantSku'],
            'barcode' => $data['variantBarcode'],
            'regular_price_minor' => $regularPriceMinor,
            'sale_price_minor' => $salePriceMinor,
            'cost_price_minor' => $costPriceMinor,
            'weight_grams' => $data['variantWeightGrams'],
            'quantity_on_hand' => $data['variantQuantity'],
            'low_stock_threshold' => $data['variantLowStockThreshold'],
            'track_quantity' => $data['variantTrackQuantity'],
            'allow_backorders' => $data['variantAllowBackorders'],
            'is_active' => $data['variantIsActive'],
            'is_default' => $data['variantIsDefault'],
            'media_ids' => $data['selectedVariantMediaIds'],
        ]);
        $this->product->refresh();
        $this->selectVariant($variant->id);
        session()->flash('status', 'Variant saved.');
    }

    #[On('media-selected')]
    public function selectMedia(int $id, ?string $url = null, ?string $context = null): void
    {
        if ($context !== 'variant-gallery') {
            return;
        }

        $asset = MediaAsset::findOrFail($id);
        Gate::authorize('view', $asset);

        if (! in_array($asset->id, $this->selectedVariantMediaIds, true)) {
            $this->selectedVariantMediaIds[] = $asset->id;
        }
    }

    public function removeMedia(int $id): void
    {
        $this->selectedVariantMediaIds = array_values(array_filter(
            $this->selectedVariantMediaIds,
            fn (int $mediaId): bool => $mediaId !== $id,
        ));
    }

    public function moveMedia(int $index, int $direction): void
    {
        $target = $index + $direction;

        if (! isset($this->selectedVariantMediaIds[$target])) {
            return;
        }

        [$this->selectedVariantMediaIds[$index], $this->selectedVariantMediaIds[$target]] = [
            $this->selectedVariantMediaIds[$target],
            $this->selectedVariantMediaIds[$index],
        ];
        $this->persistMediaOrder();
    }

    public function sortMedia(string|int $item, int $position): void
    {
        $this->authorize('update', $this->product);
        $item = (int) $item;
        $currentPosition = array_search($item, $this->selectedVariantMediaIds, true);

        if ($currentPosition === false || $position < 0 || $position >= count($this->selectedVariantMediaIds)) {
            return;
        }

        array_splice($this->selectedVariantMediaIds, $currentPosition, 1);
        array_splice($this->selectedVariantMediaIds, $position, 0, [$item]);
        $this->persistMediaOrder();
    }

    private function persistMediaOrder(): void
    {
        if (! $this->selectedVariantId) {
            return;
        }

        $this->authorize('update', $this->product);
        $variant = $this->product->variants()->findOrFail($this->selectedVariantId);

        foreach ($this->selectedVariantMediaIds as $sortOrder => $mediaId) {
            $variant->media()->where('media_asset_id', $mediaId)->update([
                'sort_order' => $sortOrder,
                'role' => $sortOrder === 0 ? 'main' : 'gallery',
            ]);
        }
    }

    public function toggle(int $id): void
    {
        $this->authorize('update', $this->product);
        $variant = $this->product->variants()->findOrFail($id);
        $variant->update(['is_active' => ! $variant->is_active]);
        if ($this->selectedVariantId === $id) {
            $this->selectVariant($id);
        }
    }

    public function render()
    {
        return view('livewire.pages.admin.catalog.products.variants', [
            'options' => $this->product->options()->with('values')->orderBy('sort_order')->get(),
            'variants' => $this->product->variants()->with('inventory', 'optionValues', 'media.asset')->orderByDesc('is_default')->orderBy('sort_order')->get(),
            'mediaAssets' => MediaAsset::query()->latest()->limit(20)->get(),
            'selectedVariantMedia' => $this->selectedVariantMediaIds === [] ? collect() : MediaAsset::query()
                ->whereKey($this->selectedVariantMediaIds)
                ->get()
                ->sortBy(fn (MediaAsset $asset): int => array_search($asset->id, $this->selectedVariantMediaIds, true))
                ->values(),
        ]);
    }

    private function refreshVariantSelection(): void
    {
        if ($this->selectedVariantId && $this->product->variants()->whereKey($this->selectedVariantId)->exists()) {
            $this->selectVariant($this->selectedVariantId);

            return;
        }

        $this->selectedVariantId = null;
    }

    private function formatPrice(int $minor): string
    {
        return number_format($minor / 100, 2, '.', '');
    }

    private function formatNullablePrice(?int $minor): ?string
    {
        return $minor === null ? null : $this->formatPrice($minor);
    }

    private function priceToMinor(?string $price): ?int
    {
        if ($price === null || trim($price) === '') {
            return null;
        }

        [$whole, $fraction] = array_pad(explode('.', trim($price), 2), 2, '');

        return ((int) $whole * 100) + (int) str_pad($fraction, 2, '0');
    }
}
