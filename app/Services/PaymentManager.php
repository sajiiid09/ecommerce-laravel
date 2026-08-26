<?php

namespace App\Services;

use App\Contracts\PaymentProvider;
use App\Models\Order;
use App\Models\Payment;
use App\Services\Payments\CodPaymentProvider;
use App\Services\Payments\StripePaymentProvider;
use Illuminate\Validation\ValidationException;

class PaymentManager
{
    public function __construct(
        private readonly CodPaymentProvider $cod,
        private readonly StripePaymentProvider $stripe,
        private readonly PaymentCredentialService $credentials,
    ) {}

    /**
     * @return array<string, array{label: string, description: string, credentials: array<string, string>}>
     */
    public function definitions(): array
    {
        return [
            'cod' => [
                'label' => 'Cash on Delivery',
                'description' => 'Pay when your order arrives.',
                'credentials' => [],
            ],
            'stripe' => [
                'label' => 'Stripe',
                'description' => 'Accept cards through Stripe-hosted Checkout.',
                'credentials' => [
                    'publishable_key' => 'Publishable key (optional for hosted Checkout)',
                    'secret_key' => 'Secret key',
                    'webhook_secret' => 'Webhook signing secret',
                ],
            ],
        ];
    }

    /**
     * @return array<string, array{label: string, description: string}>
     */
    public function available(): array
    {
        return collect($this->definitions())
            ->filter(fn (array $definition, string $method): bool => $method === 'cod'
                || ($this->credentials->isEnabled($method) && $this->credentials->isConfigured($method)))
            ->map(fn (array $definition): array => [
                'label' => $definition['label'],
                'description' => $definition['description'],
            ])
            ->all();
    }

    public function supports(string $method): bool
    {
        return array_key_exists($method, $this->available());
    }

    public function create(Order $order, string $method): Payment
    {
        return $this->provider($method)->create($order);
    }

    public function testConnection(string $method): void
    {
        if ($method === 'stripe') {
            $this->stripe->testConnection();

            return;
        }

        throw ValidationException::withMessages(['provider' => 'This payment provider does not support connection testing.']);
    }

    private function provider(string $method): PaymentProvider
    {
        return match ($method) {
            'cod' => $this->cod,
            'stripe' => $this->stripe,
            default => throw ValidationException::withMessages(['payment_method' => 'The selected payment method is unavailable.']),
        };
    }
}
