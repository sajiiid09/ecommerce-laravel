<?php

namespace App\Livewire\Pages\Account;

use Illuminate\Validation\Rule;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('components.layouts.app')]
class Dashboard extends Component
{
    public string $name = '';

    public string $email = '';

    public string $phone = '';

    public function mount(): void
    {
        $user = auth()->user();
        $this->name = (string) $user->name;
        $this->email = (string) $user->email;
        $this->phone = (string) ($user->phone ?? '');
    }

    public function updateProfile(): void
    {
        $user = auth()->user();
        $data = $this->validate([
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user->id)],
            'phone' => ['nullable', 'string', 'max:30'],
        ]);
        $user->update($data);
        session()->flash('status', 'Profile updated.');
    }

    public function render()
    {
        $user = auth()->user();

        return view('pages.account.dashboard-content', [
            'user' => $user,
            'ordersCount' => $user->orders()->count(),
            'addressesCount' => $user->addresses()->count(),
        ]);
    }
}
