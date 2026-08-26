<?php

namespace App\Services\Payments;

use App\Contracts\PaymentProvider;
use App\Models\Order;
use App\Models\Payment;

class CodPaymentProvider implements PaymentProvider
{
    public function create(Order $order): Payment
    {
        return $order->payments()->create([
            'provider' => 'cod',
            'method' => 'cod',
            'status' => 'pending',
            'amount_minor' => $order->total_minor,
            'currency' => $order->currency,
        ]);
    }
}
