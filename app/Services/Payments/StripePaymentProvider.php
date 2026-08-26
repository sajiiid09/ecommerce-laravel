<?php

namespace App\Services\Payments;

use App\Contracts\PaymentProvider;
use App\Models\Order;
use App\Models\Payment;
use App\Services\PaymentCredentialService;
use Illuminate\Support\Facades\Http;
use Illuminate\Validation\ValidationException;

class StripePaymentProvider implements PaymentProvider
{
    public function __construct(private readonly PaymentCredentialService $credentials) {}

    public function create(Order $order): Payment
    {
        $secret = $this->secret(requireEnabled: true);

        $order->loadMissing('items');
        $payload = [
            'mode' => 'payment',
            'success_url' => route('stripe.checkout.success').'?session_id={CHECKOUT_SESSION_ID}',
            'cancel_url' => route('stripe.checkout.cancel', ['order' => $order->order_number]),
            'client_reference_id' => (string) $order->id,
            'metadata[order_id]' => (string) $order->id,
            'metadata[order_number]' => $order->order_number,
            'payment_intent_data[metadata][order_id]' => (string) $order->id,
            'payment_intent_data[metadata][order_number]' => $order->order_number,
        ];

        foreach ($order->items as $index => $item) {
            $name = $item->variant_name
                ? $item->product_name.' — '.$item->variant_name
                : $item->product_name;
            $payload["line_items[$index][price_data][currency]"] = strtolower($order->currency);
            $payload["line_items[$index][price_data][product_data][name]"] = $name;
            $payload["line_items[$index][price_data][unit_amount]"] = (string) $item->unit_price_minor;
            $payload["line_items[$index][quantity]"] = (string) $item->quantity;
        }

        $response = Http::asForm()
            ->withBasicAuth($secret, '')
            ->acceptJson()
            ->timeout(15)
            ->withHeaders(['Idempotency-Key' => 'storez-order-'.$order->checkout_token])
            ->post($this->apiUrl('/v1/checkout/sessions'), $payload);

        if ($response->failed()) {
            report($response->toException());
            $this->paymentFailure('Stripe could not create the checkout session.');
        }

        $sessionId = (string) $response->json('id');
        $checkoutUrl = (string) $response->json('url');

        if (blank($sessionId) || blank($checkoutUrl)) {
            $this->paymentFailure('Stripe returned an invalid checkout session.');
        }

        return $order->payments()->create([
            'provider' => 'stripe',
            'method' => 'stripe',
            'status' => 'pending',
            'amount_minor' => $order->total_minor,
            'currency' => $order->currency,
            'provider_reference' => $sessionId,
            'metadata' => [
                'checkout_url' => $checkoutUrl,
                'session_id' => $sessionId,
            ],
        ]);
    }

    public function reconcileCheckoutSession(string $sessionId): ?Order
    {
        $session = $this->retrieveSession($sessionId);
        $payment = Payment::query()
            ->with('order')
            ->where('provider', 'stripe')
            ->where('provider_reference', $session['id'] ?? $sessionId)
            ->first();

        if (! $payment || (string) data_get($session, 'metadata.order_id') !== (string) $payment->order_id) {
            return null;
        }

        if ($payment->order->status === 'cancelled' || ($session['payment_status'] ?? null) !== 'paid') {
            return null;
        }

        $this->markPaid($payment);

        return $payment->order->fresh(['items', 'shippingAddress', 'payment']);
    }

    public function testConnection(): void
    {
        $secret = (string) ($this->credentials->credentials('stripe')['secret_key'] ?? '');
        if (blank($secret)) {
            throw ValidationException::withMessages(['provider' => 'Save a Stripe secret key before testing the connection.']);
        }

        $response = Http::withBasicAuth($secret, '')
            ->acceptJson()
            ->timeout(15)
            ->get($this->apiUrl('/v1/account'));

        if ($response->failed()) {
            report($response->toException());
            throw ValidationException::withMessages(['provider' => 'Stripe rejected the saved credentials.']);
        }
    }

    public function handleWebhook(string $payload, string $signature): void
    {
        $event = $this->verifyWebhook($payload, $signature);
        $type = (string) ($event['type'] ?? '');
        $object = (array) data_get($event, 'data.object', []);

        if (! in_array($type, ['checkout.session.completed', 'checkout.session.async_payment_succeeded'], true)) {
            return;
        }

        $sessionId = (string) ($object['id'] ?? '');
        if (blank($sessionId) || ($object['payment_status'] ?? null) !== 'paid') {
            return;
        }

        $payment = Payment::query()
            ->with('order')
            ->where('provider', 'stripe')
            ->where('provider_reference', $sessionId)
            ->first();

        if ($payment && (string) data_get($object, 'metadata.order_id') === (string) $payment->order_id) {
            $this->markPaid($payment);
        }
    }

    /**
     * @return array<string, mixed>
     */
    private function retrieveSession(string $sessionId): array
    {
        $response = Http::withBasicAuth($this->secret(), '')
            ->acceptJson()
            ->timeout(15)
            ->get($this->apiUrl('/v1/checkout/sessions/'.rawurlencode($sessionId)));

        if ($response->failed()) {
            report($response->toException());
            abort(502, 'Stripe checkout session could not be verified.');
        }

        return $response->json();
    }

    /**
     * @return array<string, mixed>
     */
    private function verifyWebhook(string $payload, string $signature): array
    {
        $secret = (string) ($this->credentials->credentials('stripe')['webhook_secret'] ?? '');
        $parts = [];

        foreach (explode(',', $signature) as $part) {
            [$key, $value] = array_pad(explode('=', $part, 2), 2, null);
            if ($key !== null && $value !== null) {
                $parts[$key][] = $value;
            }
        }

        $timestamp = (int) ($parts['t'][0] ?? 0);
        $expected = $timestamp > 0 ? hash_hmac('sha256', $timestamp.'.'.$payload, $secret) : '';
        $valid = filled($secret)
            && $timestamp > 0
            && abs(now()->timestamp - $timestamp) <= 300
            && collect($parts['v1'] ?? [])->contains(fn (string $value): bool => hash_equals($expected, $value));

        abort_unless($valid, 400, 'Invalid Stripe webhook signature.');

        return json_decode($payload, true, flags: JSON_THROW_ON_ERROR);
    }

    private function markPaid(Payment $payment): void
    {
        if ($payment->status === 'paid' || $payment->order->status === 'cancelled') {
            return;
        }

        $payment->update(['status' => 'paid', 'paid_at' => now()]);
        $payment->order()->update(['payment_status' => 'paid']);
    }

    private function apiUrl(string $path): string
    {
        return rtrim((string) config('services.stripe.api_url'), '/').$path;
    }

    private function secret(bool $requireEnabled = false): string
    {
        $record = $this->credentials->get('stripe');
        $secret = (string) ($this->credentials->credentials('stripe')['secret_key'] ?? '');

        abort_unless($record && (! $requireEnabled || $record->enabled) && filled($secret), 404);

        return $secret;
    }

    private function paymentFailure(string $message): never
    {
        throw ValidationException::withMessages(['payment_method' => $message]);
    }
}
