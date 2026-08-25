<?php

namespace App\Livewire\Pages\Admin\Catalog\Products;

use App\Models\Product;
use App\Models\ProductOption;
use App\Services\ProductVariantService;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
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

    public int $variantRegularPriceMinor = 0;

    public ?int $variantSalePriceMinor = null;

    public ?int $variantCompareAtPriceMinor = null;

    public ?int $variantCostPriceMinor = null;

    public ?int $variantWeightGrams = null;

    public int $variantQuantity = 0;

    public int $variantLowStockThreshold = 0;

    public bool $variantTrackQuantity = true;

    public bool $variantAllowBackorders = false;

    public bool $variantIsActive = true;

    public bool $variantIsDefault = false;

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
        $this->validate(['optionName' => 'required|string|max:100']);
        $this->product->options()->create(['name' => $this->optionName, 'slug' => Str::slug($this->optionName)]);
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
        }$option = ProductOption::whereBelongsTo($this->product)->findOrFail($optionId);
        $option->values()->create(['value' => $value, 'slug' => Str::slug($value)]);
        $this->optionValues[$optionId] = '';
        $this->product->refresh();
    }

    public function removeOption(int $optionId): void
    {
        $this->authorize('update', $this->product);
        ProductOption::whereBelongsTo($this->product)->findOrFail($optionId)->delete();
        $this->product->refresh();
    }

    public function removeValue(int $optionId, int $valueId): void
    {
        $this->authorize('update', $this->product);
        $option = ProductOption::whereBelongsTo($this->product)->findOrFail($optionId);
        $option->values()->findOrFail($valueId)->delete();
        $this->product->refresh();
    }

    public function generate(): void
    {
        $this->authorize('update', $this->product);
        app(ProductVariantService::class)->generate($this->product);
        $this->product->refresh();
    }

    public function selectVariant(int $id): void
    {
        $this->authorize('update', $this->product);
        $variant = $this->product->variants()->with('inventory')->findOrFail($id);
        $this->selectedVariantId = $variant->id;
        $this->variantSku = (string) $variant->sku;
        $this->variantBarcode = (string) ($variant->barcode ?? '');
        $this->variantRegularPriceMinor = (int) ($variant->regular_price_minor ?? 0);
        $this->variantSalePriceMinor = $variant->sale_price_minor;
        $this->variantCompareAtPriceMinor = $variant->compare_at_price_minor;
        $this->variantCostPriceMinor = $variant->cost_price_minor;
        $this->variantWeightGrams = $variant->weight_grams;
        $this->variantQuantity = (int) ($variant->inventory?->quantity_on_hand ?? 0);
        $this->variantLowStockThreshold = (int) ($variant->inventory?->low_stock_threshold ?? 0);
        $this->variantTrackQuantity = (bool) ($variant->inventory?->track_quantity ?? true);
        $this->variantAllowBackorders = (bool) ($variant->inventory?->allow_backorders ?? false);
        $this->variantIsActive = (bool) $variant->is_active;
        $this->variantIsDefault = (bool) $variant->is_default;
    }

    public function saveVariant(): void
    {
        $this->authorize('update', $this->product);
        $variant = $this->product->variants()->findOrFail($this->selectedVariantId);
        $data = $this->validate(['variantSku' => ['required', 'string', 'max:100', Rule::unique('product_variants', 'sku')->ignore($variant->id)], 'variantBarcode' => ['nullable', 'string', 'max:100'], 'variantRegularPriceMinor' => ['required', 'integer', 'min:0'], 'variantSalePriceMinor' => ['nullable', 'integer', 'min:0'], 'variantCompareAtPriceMinor' => ['nullable', 'integer', 'min:0'], 'variantCostPriceMinor' => ['nullable', 'integer', 'min:0'], 'variantWeightGrams' => ['nullable', 'integer', 'min:0'], 'variantQuantity' => ['required', 'integer', 'min:0'], 'variantLowStockThreshold' => ['required', 'integer', 'min:0'], 'variantTrackQuantity' => ['boolean'], 'variantAllowBackorders' => ['boolean'], 'variantIsActive' => ['boolean'], 'variantIsDefault' => ['boolean']]);
        app(ProductVariantService::class)->save($variant, ['sku' => $data['variantSku'], 'barcode' => $data['variantBarcode'], 'regular_price_minor' => $data['variantRegularPriceMinor'], 'sale_price_minor' => $data['variantSalePriceMinor'], 'compare_at_price_minor' => $data['variantCompareAtPriceMinor'], 'cost_price_minor' => $data['variantCostPriceMinor'], 'weight_grams' => $data['variantWeightGrams'], 'quantity_on_hand' => $data['variantQuantity'], 'low_stock_threshold' => $data['variantLowStockThreshold'], 'track_quantity' => $data['variantTrackQuantity'], 'allow_backorders' => $data['variantAllowBackorders'], 'is_active' => $data['variantIsActive'], 'is_default' => $data['variantIsDefault']]);
        $this->product->refresh();
        $this->selectVariant($variant->id);
        session()->flash('status', 'Variant saved.');
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
        return view('livewire.pages.admin.catalog.products.variants', ['options' => $this->product->options()->with('values')->orderBy('sort_order')->get(), 'variants' => $this->product->variants()->with('inventory', 'optionValues')->get()]);
    }
}
