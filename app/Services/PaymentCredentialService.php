<?php

namespace App\Services;

use App\Models\PaymentProviderCredential;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Schema;

class PaymentCredentialService
{
    /**
     * @return array<string, mixed>
     */
    public function credentials(string $provider): array
    {
        return (array) ($this->get($provider)?->credentials ?? []);
    }

    public function get(string $provider): ?PaymentProviderCredential
    {
        if (! Schema::hasTable('payment_provider_credentials')) {
            return null;
        }

        return PaymentProviderCredential::query()->where('provider', $provider)->first();
    }

    public function isEnabled(string $provider): bool
    {
        return (bool) $this->get($provider)?->enabled;
    }

    public function isConfigured(string $provider): bool
    {
        $record = $this->get($provider);
        $credentials = $this->credentials($provider);

        return match ($provider) {
            'stripe' => $record !== null
                && in_array($record->mode, ['test', 'live'], true)
                && str_starts_with((string) ($credentials['secret_key'] ?? ''), 'sk_'.$record->mode.'_')
                && str_starts_with((string) ($credentials['webhook_secret'] ?? ''), 'whsec_')
                && (blank($credentials['publishable_key'] ?? null)
                    || str_starts_with((string) $credentials['publishable_key'], 'pk_'.$record->mode.'_')),
            default => false,
        };
    }

    /**
     * @param  array<string, mixed>  $credentials
     */
    public function save(
        string $provider,
        array $credentials,
        bool $enabled,
        string $mode,
        ?User $user = null,
    ): PaymentProviderCredential {
        $setting = PaymentProviderCredential::query()->firstOrNew(['provider' => $provider]);
        $setting->fill([
            'enabled' => $enabled,
            'mode' => $mode,
            'credentials' => $credentials,
            'updated_by' => $user?->id ?? auth()->id(),
        ]);
        $setting->save();

        Cache::forget($this->availabilityCacheKey($provider));

        return $setting->fresh('updatedBy');
    }

    public function clear(string $provider): void
    {
        PaymentProviderCredential::query()->where('provider', $provider)->delete();
        Cache::forget($this->availabilityCacheKey($provider));
    }

    /**
     * @return array<string, string>
     */
    public function masked(string $provider): array
    {
        $credentials = $this->credentials($provider);

        return collect($credentials)
            ->mapWithKeys(fn (mixed $value, string $key): array => [$key => $this->mask((string) $value)])
            ->all();
    }

    private function mask(string $value): string
    {
        if ($value === '') {
            return '';
        }

        return str_repeat('•', 8).substr($value, -4);
    }

    private function availabilityCacheKey(string $provider): string
    {
        return 'payments:availability:'.$provider;
    }
}
