<?php

namespace App\Livewire\Pages\Store;

use App\Models\UserAddress;
use App\Services\CartService;
use App\Services\CouponService;
use App\Services\OrderService;
use App\Services\PaymentManager;
use App\Support\StorefrontDemoData;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Checkout extends Component
{
    public int $step = 1;

    public string $customer_name = '';

    public string $customer_email = '';

    public string $customer_phone = '';

    public string $address_line = '';

    public string $city = 'Dhaka';

    public string $district = '';

    public string $postal_code = '';

    public string $country = 'BD';

    public ?int $selectedAddressId = null;

    public string $delivery_method = 'standard';

    public string $payment_method = 'cod';

    public string $checkout_token = '';

    public string $couponCode = '';

    public string $appliedCouponCode = '';

    public function mount(): void
    {
        $user = auth()->user();
        $this->checkout_token = (string) Str::uuid();
        $this->customer_name = (string) ($user?->name ?? '');
        $this->customer_email = (string) ($user?->email ?? '');
        $this->customer_phone = (string) ($user?->phone ?? '');

        if ($user) {
            $defaultAddress = $user->addresses()->where('is_default', true)->first();

            if ($defaultAddress) {
                $this->selectAddress($defaultAddress->id);
            }
        }
    }

    public function selectAddress(int $addressId): void
    {
        $address = auth()->user()?->addresses()->findOrFail($addressId);
        abort_unless($address instanceof UserAddress, 404);

        $this->selectedAddressId = $address->id;
        $this->fillAddressFields($address);
        $this->resetValidation();
    }

    public function useNewAddress(): void
    {
        $this->selectedAddressId = null;
        $this->address_line = '';
        $this->city = 'Dhaka';
        $this->district = '';
        $this->postal_code = '';
        $this->country = 'BD';
        $this->resetValidation('selectedAddressId');
    }

    public function nextStep(PaymentManager $payments): void
    {
        if (! $this->validateCurrentStep($payments)) {
            return;
        }

        $this->step = min(4, $this->step + 1);
    }

    public function previousStep(): void
    {
        $this->step = max(1, $this->step - 1);
    }

    public function applyCoupon(CouponService $coupons): void
    {
        $this->validate(['couponCode' => ['required', 'string', 'max:50']]);

        try {
            $cart = app(CartService::class)->current();
            $quote = $coupons->quote($this->couponCode, $cart, auth()->user(), $this->customer_email);
            $this->appliedCouponCode = $quote['code'];
            $this->couponCode = $quote['code'];
            $this->resetValidation('couponCode');
        } catch (ValidationException $exception) {
            $this->setErrorBag($exception->validator->errors());
        }
    }

    public function removeCoupon(): void
    {
        $this->couponCode = '';
        $this->appliedCouponCode = '';
        $this->resetValidation('couponCode');
    }

    public function placeOrder(OrderService $orders, PaymentManager $payments): void
    {
        if (! config('features.guest_checkout') && ! auth()->check()) {
            $this->redirectRoute('login');

            return;
        }

        if (! $this->resolveSelectedAddress()) {
            return;
        }

        $this->validate([
            'customer_name' => ['required', 'string', 'max:255'],
            'customer_email' => ['required', 'email', 'max:255'],
            'customer_phone' => ['required', 'string', 'max:30'],
            'address_line' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'district' => ['nullable', 'string', 'max:100'],
            'postal_code' => ['nullable', 'string', 'max:20'],
            'delivery_method' => ['required', 'in:standard,express'],
            'payment_method' => ['required', Rule::in(array_keys($payments->available()))],
        ]);

        $order = $orders->place([
            'customer_name' => $this->customer_name,
            'customer_email' => $this->customer_email,
            'customer_phone' => $this->customer_phone,
            'address_line' => $this->address_line,
            'city' => $this->city,
            'district' => $this->district,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
            'delivery_method' => $this->delivery_method,
            'payment_method' => $this->payment_method,
            'checkout_token' => $this->checkout_token,
            'coupon_code' => $this->appliedCouponCode ?: null,
        ], auth()->user());

        $this->dispatch('cart-updated');
        session()->put('last_order_number', $order->order_number);

        if ($order->payment_method === 'stripe') {
            $checkoutUrl = (string) data_get($order->payment?->metadata, 'checkout_url');
            abort_unless(filled($checkoutUrl), 502, 'Stripe checkout URL was not returned.');
            $this->redirect($checkoutUrl, navigate: false);

            return;
        }

        $this->redirect(route('store.order-success', ['order' => $order->order_number]));
    }

    public function render()
    {
        if (! Schema::hasTable('carts')) {
            $items = collect(StorefrontDemoData::cartItems())->map(fn (array $item): array => [
                ...$item,
                'id' => $item['id'],
                'variant_id' => null,
                'product_id' => $item['id'],
                'sku' => null,
                'variant' => null,
                'price' => $item['price'] * 100,
                'quantity' => $item['quantity'],
                'line_total' => $item['price'] * 100 * $item['quantity'],
            ])->all();

            return view('pages.store.checkout-content', [
                'cart' => null,
                'items' => $items,
                'subtotal' => collect($items)->sum('line_total'),
                'shipping' => $this->delivery_method === 'express' ? 6000 : 0,
                'discount' => 0,
                'couponQuote' => null,
                'savedAddresses' => auth()->check() ? auth()->user()->addresses()->latest()->get() : collect(),
                'paymentMethods' => app(PaymentManager::class)->available(),
            ]);
        }

        $cart = app(CartService::class)->current();
        $subtotal = app(CartService::class)->subtotal($cart);
        $couponQuote = $this->appliedCouponCode === ''
            ? null
            : app(CouponService::class)->quote($this->appliedCouponCode, $cart, auth()->user(), $this->customer_email);
        $discount = $couponQuote['discount_minor'] ?? 0;

        return view('pages.store.checkout-content', [
            'cart' => $cart,
            'items' => app(CartService::class)->present($cart),
            'savedAddresses' => auth()->check() ? auth()->user()->addresses()->latest()->get() : collect(),
            'subtotal' => $subtotal,
            'shipping' => $this->delivery_method === 'express' ? 6000 : 0,
            'discount' => $discount,
            'couponQuote' => $couponQuote,
            'paymentMethods' => app(PaymentManager::class)->available(),
        ]);
    }

    private function validateCurrentStep(PaymentManager $payments): bool
    {
        if ($this->step === 1 && ! $this->resolveSelectedAddress()) {
            return false;
        }

        $rules = match ($this->step) {
            1 => ['customer_name' => ['required', 'string', 'max:255'], 'customer_email' => ['required', 'email', 'max:255'], 'customer_phone' => ['required', 'string', 'max:30'], 'address_line' => ['required', 'string', 'max:500'], 'city' => ['required', 'string', 'max:100']],
            2 => ['delivery_method' => ['required', 'in:standard,express']],
            3 => ['payment_method' => ['required', Rule::in(array_keys($payments->available()))]],
            default => [],
        };
        $this->validate($rules);

        return true;
    }

    private function resolveSelectedAddress(): bool
    {
        if ($this->selectedAddressId === null) {
            return true;
        }

        if (! auth()->check()) {
            $this->addError('selectedAddressId', 'Please enter a new shipping address.');

            return false;
        }

        $address = auth()->user()->addresses()->find($this->selectedAddressId);

        if (! $address) {
            $this->addError('selectedAddressId', 'That saved address is no longer available.');

            return false;
        }

        $this->fillAddressFields($address);

        return true;
    }

    private function fillAddressFields(UserAddress $address): void
    {
        $this->customer_name = $address->recipient_name;
        $this->customer_phone = $address->phone;
        $this->address_line = $address->address_line;
        $this->city = $address->city;
        $this->district = (string) ($address->district ?? '');
        $this->postal_code = (string) ($address->postal_code ?? '');
        $this->country = $address->country;
    }
}
