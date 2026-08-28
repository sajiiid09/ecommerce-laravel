<?php

namespace App\Livewire\Pages\Admin\Coupons;

use App\Enums\CouponDiscountType;
use App\Models\Category;
use App\Models\Coupon;
use App\Models\Product;
use App\Services\CouponService;
use Illuminate\Contracts\View\View;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.admin')]
class Index extends Component
{
    use WithPagination;

    public string $searchQuery = '';

    public string $status = 'all';

    public int $perPage = 15;

    public ?int $editingId = null;

    public string $code = '';

    public string $discountType = 'percentage';

    public string $percentage = '10';

    public string $amount = '';

    public string $minimumSubtotal = '0';

    public string $scope = 'storewide';

    public array $productIds = [];

    public array $categoryIds = [];

    public string $startsAt = '';

    public string $endsAt = '';

    public string $usageLimit = '';

    public string $perCustomerLimit = '';

    public bool $isActive = true;

    public function updatedSearchQuery(): void
    {
        $this->resetPage();
    }

    public function updatedStatus(): void
    {
        $this->resetPage();
    }

    public function updatedPerPage(): void
    {
        $this->resetPage();
    }

    public function openCreate(): void
    {
        Gate::authorize('create', Coupon::class);
        $this->resetForm();
        $this->dispatch('open-modal', id: 'coupon-editor');
    }

    public function openEdit(int $couponId): void
    {
        $coupon = Coupon::with(['products:id', 'categories:id'])->findOrFail($couponId);
        Gate::authorize('update', $coupon);
        $this->editingId = $coupon->id;
        $this->code = $coupon->code;
        $this->discountType = $coupon->discount_type->value;
        $this->percentage = (string) ($coupon->percentage ?? '');
        $this->amount = $this->minorToInput($coupon->amount_minor);
        $this->minimumSubtotal = $this->minorToInput($coupon->minimum_subtotal_minor);
        $this->scope = $coupon->products->isNotEmpty() || $coupon->categories->isNotEmpty() ? 'targeted' : 'storewide';
        $this->productIds = $coupon->products->modelKeys();
        $this->categoryIds = $coupon->categories->modelKeys();
        $this->startsAt = $coupon->starts_at?->format('Y-m-d\TH:i') ?? '';
        $this->endsAt = $coupon->ends_at?->format('Y-m-d\TH:i') ?? '';
        $this->usageLimit = (string) ($coupon->usage_limit ?? '');
        $this->perCustomerLimit = (string) ($coupon->per_customer_limit ?? '');
        $this->isActive = $coupon->is_active;
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'coupon-editor');
    }

    public function generateCode(CouponService $coupons): void
    {
        $this->code = $coupons->generateCode();
    }

    public function saveCoupon(CouponService $coupons): void
    {
        $coupon = $this->editingId ? Coupon::findOrFail($this->editingId) : new Coupon;
        Gate::authorize($this->editingId ? 'update' : 'create', $coupon);
        $this->code = $coupons->normalizeCode($this->code);

        $this->validate([
            'code' => ['required', 'string', 'max:50', 'regex:/^[A-Z0-9][A-Z0-9_-]{2,49}$/', Rule::unique('coupons', 'code')->ignore($coupon->id)],
            'discountType' => ['required', Rule::in(array_map(fn (CouponDiscountType $type): string => $type->value, CouponDiscountType::cases()))],
            'percentage' => ['nullable', 'integer', 'min:1', 'max:100', Rule::requiredIf(fn (): bool => $this->discountType === 'percentage')],
            'amount' => ['nullable', 'numeric', 'min:0.01', Rule::requiredIf(fn (): bool => $this->discountType === 'fixed')],
            'minimumSubtotal' => ['required', 'numeric', 'min:0'],
            'scope' => ['required', Rule::in(['storewide', 'targeted'])],
            'productIds' => ['array'], 'productIds.*' => ['integer', 'exists:products,id'],
            'categoryIds' => ['array'], 'categoryIds.*' => ['integer', 'exists:categories,id'],
            'startsAt' => ['nullable', 'date'], 'endsAt' => ['nullable', 'date', 'after_or_equal:startsAt'],
            'usageLimit' => ['nullable', 'integer', 'min:1'], 'perCustomerLimit' => ['nullable', 'integer', 'min:1'],
        ]);

        DB::transaction(function () use ($coupon, $coupons): void {
            $coupon->fill([
                'code' => $coupons->normalizeCode($this->code),
                'discount_type' => $this->discountType,
                'percentage' => $this->discountType === 'percentage' ? (int) $this->percentage : null,
                'amount_minor' => $this->discountType === 'fixed' ? $this->toMinor($this->amount) : null,
                'minimum_subtotal_minor' => $this->toMinor($this->minimumSubtotal),
                'starts_at' => $this->startsAt ?: null, 'ends_at' => $this->endsAt ?: null,
                'usage_limit' => $this->usageLimit !== '' ? (int) $this->usageLimit : null,
                'per_customer_limit' => $this->perCustomerLimit !== '' ? (int) $this->perCustomerLimit : null,
                'is_active' => $this->isActive,
            ])->save();
            $coupon->products()->sync($this->scope === 'targeted' ? $this->productIds : []);
            $coupon->categories()->sync($this->scope === 'targeted' ? $this->categoryIds : []);
        });

        $this->dispatch('close-modal', id: 'coupon-editor');
        $this->resetForm();
    }

    public function toggleActive(int $couponId): void
    {
        $coupon = Coupon::findOrFail($couponId);
        Gate::authorize('update', $coupon);
        $coupon->update(['is_active' => ! $coupon->is_active]);
    }

    public function deleteCoupon(int $couponId): void
    {
        $coupon = Coupon::findOrFail($couponId);
        Gate::authorize('delete', $coupon);
        $coupon->delete();
    }

    public function render(CouponService $coupons): View
    {
        Gate::authorize('viewAny', Coupon::class);
        $now = now();
        $couponRows = Coupon::query()
            ->withCount(['products', 'categories'])
            ->withCount(['orders as redeemed_count' => fn (Builder $query) => $query->where('status', '!=', 'cancelled')])
            ->when($this->searchQuery !== '', fn (Builder $query) => $query->where('code', 'like', '%'.strtoupper($this->searchQuery).'%'))
            ->when($this->status !== 'all', function (Builder $query) use ($now): void {
                match ($this->status) {
                    'active' => $query->where('is_active', true)->where(fn (Builder $q) => $q->whereNull('starts_at')->orWhere('starts_at', '<=', $now))->where(fn (Builder $q) => $q->whereNull('ends_at')->orWhere('ends_at', '>=', $now)),
                    'scheduled' => $query->where('is_active', true)->where('starts_at', '>', $now),
                    'expired' => $query->where('is_active', true)->where('ends_at', '<', $now),
                    'inactive' => $query->where('is_active', false), default => null,
                };
            })->latest()->paginate($this->perPage);

        return view('livewire.pages.admin.coupons.index', [
            'couponRows' => $couponRows,
            'products' => Product::query()->published()->orderBy('name')->get(['id', 'name']),
            'categories' => Category::query()->active()->orderBy('name')->get(['id', 'name']),
            'couponService' => $coupons,
        ]);
    }

    private function resetForm(): void
    {
        $this->reset(['editingId', 'code', 'amount', 'startsAt', 'endsAt', 'usageLimit', 'perCustomerLimit', 'productIds', 'categoryIds']);
        $this->discountType = 'percentage';
        $this->percentage = '10';
        $this->minimumSubtotal = '0';
        $this->scope = 'storewide';
        $this->isActive = true;
        $this->resetValidation();
    }

    private function toMinor(string $value): int
    {
        $value = str_replace(',', '', trim($value));
        if ($value === '') {
            return 0;
        }
        [$whole, $fraction] = array_pad(explode('.', $value, 2), 2, '0');

        return ((int) $whole * 100) + (int) str_pad(substr($fraction, 0, 2), 2, '0');
    }

    private function minorToInput(?int $minor): string
    {
        return $minor === null ? '' : number_format($minor / 100, 2, '.', '');
    }
}
