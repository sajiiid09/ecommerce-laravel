<?php

namespace App\Livewire\Pages\Account;

use App\Models\District;
use App\Models\User;
use App\Models\UserAddress;
use Illuminate\Contracts\View\View;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Addresses extends Component
{
    public ?int $editingAddressId = null;

    public string $label = '';

    public string $recipientName = '';

    public string $phone = '';

    public string $addressLine = '';

    public string $city = '';

    public string $district = '';

    public ?int $districtId = null;

    public string $postalCode = '';

    public string $country = 'BD';

    public bool $isDefault = false;

    public function openCreate(): void
    {
        $this->resetForm();
        $this->dispatch('open-modal', id: 'address-editor');
    }

    public function openEdit(int $addressId): void
    {
        $address = auth()->user()->addresses()->findOrFail($addressId);
        $this->editingAddressId = $address->id;
        $this->label = $address->label;
        $this->recipientName = $address->recipient_name;
        $this->phone = $address->phone;
        $this->addressLine = $address->address_line;
        $this->city = $address->city;
        $this->district = $address->districtName();
        $this->districtId = $address->district_id ?? $address->legacyDistrictId();
        $this->postalCode = (string) ($address->postal_code ?? '');
        $this->country = $address->country;
        $this->isDefault = $address->is_default;
        $this->resetValidation();
        $this->dispatch('open-modal', id: 'address-editor');
    }

    public function saveAddress(): void
    {
        $data = $this->validate([
            'label' => ['required', 'string', 'max:100'],
            'recipientName' => ['required', 'string', 'max:255'],
            'phone' => ['required', 'string', 'max:30'],
            'addressLine' => ['required', 'string', 'max:500'],
            'city' => ['required', 'string', 'max:100'],
            'districtId' => $this->editingAddressId === null
                ? ['required', 'integer', Rule::exists('districts', 'id')->where(fn ($query) => $query->where('is_active', true))]
                : ['nullable', 'integer', Rule::exists('districts', 'id')],
            'postalCode' => ['nullable', 'string', 'max:20'],
            'country' => ['required', 'string', 'size:2', 'regex:/^[A-Z]{2}$/'],
            'isDefault' => ['boolean'],
        ]);

        $district = $data['districtId'] === null ? null : District::query()->find($data['districtId']);
        $currentDistrictId = $this->editingAddressId === null
            ? null
            : (int) UserAddress::query()->whereKey($this->editingAddressId)->value('district_id');
        if ($district && ! $district->is_active && $district->id !== $currentDistrictId) {
            $this->addError('districtId', 'Please select an active district.');

            return;
        }

        $user = auth()->user();
        $address = DB::transaction(function () use ($data, $user, $district): UserAddress {
            $user = User::query()->whereKey($user->id)->lockForUpdate()->firstOrFail();
            $address = $this->editingAddressId
                ? $user->addresses()->findOrFail($this->editingAddressId)
                : new UserAddress(['user_id' => $user->id]);

            $wasDefault = $address->exists && $address->is_default;
            $address->fill([
                'user_id' => $user->id,
                'label' => $data['label'],
                'recipient_name' => $data['recipientName'],
                'phone' => $data['phone'],
                'address_line' => $data['addressLine'],
                'city' => $data['city'],
                'district_id' => $district?->id,
                'district' => $district?->name ?? $address->districtName(),
                'postal_code' => $data['postalCode'] ?: null,
                'country' => strtoupper($data['country']),
                'is_default' => $wasDefault,
            ]);
            $address->save();

            if ($data['isDefault'] || $wasDefault) {
                $address->makeDefault();
            }

            return $address;
        });

        $this->dispatch('close-modal', id: 'address-editor');
        $this->resetForm();
        session()->flash('status', $address->wasRecentlyCreated ? 'Address added.' : 'Address updated.');
    }

    public function setDefault(int $addressId): void
    {
        $address = auth()->user()->addresses()->findOrFail($addressId);
        $address->makeDefault();
        session()->flash('status', 'Default address updated.');
    }

    public function deleteAddress(int $addressId): void
    {
        auth()->user()->addresses()->findOrFail($addressId)->delete();
        session()->flash('status', 'Address deleted.');
    }

    public function render(): View
    {
        $districtOptions = District::query()
            ->where('is_active', true)
            ->orderBy('sort_order')
            ->orderBy('name')
            ->get();
        $selectedDistrict = $this->districtId === null ? null : District::query()->find($this->districtId);
        if ($selectedDistrict && ! $selectedDistrict->is_active && ! $districtOptions->contains('id', $selectedDistrict->id)) {
            $districtOptions->prepend($selectedDistrict);
        }

        return view('pages.account.addresses-content', [
            'addresses' => auth()->user()->addresses()
                ->orderByDesc('is_default')
                ->latest('created_at')
                ->latest('id')
                ->get(),
            'districts' => $districtOptions,
        ]);
    }

    private function resetForm(): void
    {
        $this->reset([
            'editingAddressId', 'label', 'recipientName', 'phone', 'addressLine',
            'city', 'district', 'districtId', 'postalCode', 'isDefault',
        ]);
        $this->country = 'BD';
        $this->resetValidation();
    }
}
