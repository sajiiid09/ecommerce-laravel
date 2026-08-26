<?php

namespace App\Contracts;

use App\Models\Order;
use App\Models\Payment;

interface PaymentProvider
{
    public function create(Order $order): Payment;
}
