<?php

namespace App\Http\Controllers;

use App\Models\Order;
use App\Services\OrderService;
use App\Services\PaymentCredentialService;
use App\Services\Payments\StripePaymentProvider;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class StripeCheckoutController extends Controller
{
    public function success(Request $request, StripePaymentProvider $stripe): RedirectResponse
    {
        $sessionId = (string) $request->query('session_id');
        abort_if(blank($sessionId), 404);

        $order = $stripe->reconcileCheckoutSession($sessionId);
        abort_unless($order, 404);
        abort_unless(auth()->id() === $order->user_id || session('last_order_number') === $order->order_number, 403);

        session()->put('last_order_number', $order->order_number);
        session()->flash('notify', [
            'content' => 'Payment completed successfully. Order placed.',
            'type' => 'success',
        ]);

        return redirect()->route('store.order-success', ['order' => $order->order_number]);
    }

    public function cancel(Request $request, OrderService $orders): RedirectResponse
    {
        $order = Order::query()
            ->with('payment')
            ->where('order_number', $request->query('order'))
            ->firstOrFail();

        abort_unless(auth()->id() === $order->user_id || session('last_order_number') === $order->order_number, 403);

        if ($order->status === 'pending' && $order->payment?->provider === 'stripe' && $order->payment->status === 'pending') {
            $orders->transition($order, 'cancelled', 'Stripe Checkout was cancelled by the customer.');
        }

        return redirect()
            ->route('store.checkout')
            ->with('payment_cancelled', 'Stripe checkout was cancelled. Your order was not completed.');
    }

    public function webhook(Request $request, StripePaymentProvider $stripe, PaymentCredentialService $credentials): JsonResponse
    {
        abort_unless($credentials->isConfigured('stripe'), 404);
        $stripe->handleWebhook($request->getContent(), (string) $request->header('Stripe-Signature'));

        return response()->json(['received' => true]);
    }
}
