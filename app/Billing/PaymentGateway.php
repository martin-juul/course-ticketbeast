<?php

namespace App\Billing;

interface PaymentGateway
{
    public const TEST_CARD_NUMBER = '';

    public function charge($amount, $token);

    public function getValidTestToken($cardNumber);

    public function newChargesDuring(callable $callback);
}
