<?php

namespace App\Livewire\Pages\Admin\Settings;

use App\Models\PaymentProviderCredential;
use App\Services\PaymentCredentialService;
use App\Services\PaymentManager;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.admin')]
class Payments extends Component
{
    public bool $enabled = false;

    public string $mode = 'test';

    public string $publishable_key = '';

    public string $secret_key = '';

    public string $webhook_secret = '';

    public bool $clear_publishable_key = false;

    public bool $clear_secret_key = false;

    public bool $clear_webhook_secret = false;

    /** @var array<string, string> */
    public array $maskedCredentials = [];

    public ?string $updatedAt = null;

    public ?string $updatedBy = null;

    public function mount(PaymentCredentialService $credentials): void
    {
        $this->refreshStatus($credentials);
    }

    public function save(PaymentCredentialService $credentials): void
    {
        $existing = $credentials->get('stripe');
        if ($existing) {
            Gate::authorize('update', $existing);
        } else {
            Gate::authorize('create', PaymentProviderCredential::class);
        }

        $this->validate([
            'mode' => ['required', Rule::in(['test', 'live'])],
            'publishable_key' => ['nullable', 'string', 'max:255'],
            'secret_key' => ['nullable', 'string', 'max:255'],
            'webhook_secret' => ['nullable', 'string', 'max:255'],
            'enabled' => ['boolean'],
            'clear_publishable_key' => ['boolean'],
            'clear_secret_key' => ['boolean'],
            'clear_webhook_secret' => ['boolean'],
        ]);

        $stored = $credentials->credentials('stripe');
        $values = [
            'publishable_key' => trim($this->publishable_key),
            'secret_key' => trim($this->secret_key),
            'webhook_secret' => trim($this->webhook_secret),
        ];

        foreach ($values as $key => $value) {
            if ($this->{'clear_'.$key}) {
                unset($stored[$key]);
            } elseif ($value !== '') {
                $stored[$key] = $value;
            }
        }

        $this->validateCredentialPrefixes($stored);

        if ($this->enabled && (blank($stored['secret_key'] ?? null) || blank($stored['webhook_secret'] ?? null))) {
            $this->addError('enabled', 'A Stripe secret key and webhook signing secret are required before enabling Stripe.');

            return;
        }

        $record = $credentials->save('stripe', $stored, $this->enabled, $this->mode);
        $this->clearSecretInputs();
        $this->setStatus($record, $credentials);

        session()->flash('status', 'Stripe payment settings saved.');
    }

    public function testConnection(PaymentManager $payments): void
    {
        Gate::authorize('viewAny', PaymentProviderCredential::class);
        $payments->testConnection('stripe');

        session()->flash('status', 'Stripe credentials are valid and the API connection succeeded.');
    }

    public function clearCredentials(PaymentCredentialService $credentials): void
    {
        $record = $credentials->get('stripe');
        if ($record) {
            Gate::authorize('delete', $record);
        } else {
            Gate::authorize('viewAny', PaymentProviderCredential::class);
        }

        $credentials->clear('stripe');
        $this->enabled = false;
        $this->mode = 'test';
        $this->clearSecretInputs();
        $this->refreshStatus($credentials);

        session()->flash('status', 'Stripe credentials cleared and Stripe disabled.');
    }

    public function render(PaymentManager $payments)
    {
        return view('livewire.pages.admin.settings.payments', [
            'definition' => $payments->definitions()['stripe'],
        ]);
    }

    private function refreshStatus(PaymentCredentialService $credentials): void
    {
        $record = PaymentProviderCredential::query()
            ->with('updatedBy')
            ->where('provider', 'stripe')
            ->first();

        $this->enabled = (bool) $record?->enabled;
        $this->mode = (string) ($record?->mode ?? 'test');
        $this->setStatus($record, $credentials);
    }

    private function setStatus(?PaymentProviderCredential $record, ?PaymentCredentialService $credentials = null): void
    {
        $this->maskedCredentials = $credentials?->masked('stripe') ?? [];
        $this->updatedAt = $record?->updated_at?->format('M j, Y g:i A');
        $this->updatedBy = $record?->updatedBy?->name;
    }

    private function clearSecretInputs(): void
    {
        $this->publishable_key = '';
        $this->secret_key = '';
        $this->webhook_secret = '';
        $this->clear_publishable_key = false;
        $this->clear_secret_key = false;
        $this->clear_webhook_secret = false;
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    private function validateCredentialPrefixes(array $credentials): void
    {
        $prefixes = [
            'publishable_key' => 'pk_'.$this->mode.'_',
            'secret_key' => 'sk_'.$this->mode.'_',
        ];

        foreach ($prefixes as $key => $prefix) {
            if (filled($credentials[$key] ?? null) && ! str_starts_with((string) $credentials[$key], $prefix)) {
                $this->addError($key, 'This key must match the selected '.$this->mode.' mode.');
            }
        }

        if (filled($credentials['webhook_secret'] ?? null) && ! str_starts_with((string) $credentials['webhook_secret'], 'whsec_')) {
            $this->addError('webhook_secret', 'Webhook signing secrets must start with whsec_.');
        }

        if ($this->getErrorBag()->isNotEmpty()) {
            $this->throwValidationException();
        }
    }

    private function throwValidationException(): never
    {
        throw ValidationException::withMessages($this->getErrorBag()->toArray());
    }
}
