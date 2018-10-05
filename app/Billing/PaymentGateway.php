<?php

namespace App\Billing;

interface PaymentGateway
{
    public function charge($amount, $token);

    public function getValidTestToken($cardNumber = '');

    public function newChargesDuring(callable $callback);
}
