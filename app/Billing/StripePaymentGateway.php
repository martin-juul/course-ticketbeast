<?php

namespace App\Billing;

use Stripe\Charge;

class StripePaymentGateway implements PaymentGateway
{
    /** @var string */
    private $apiKey;

    public function __construct(string $apiKey)
    {
        $this->apiKey = $apiKey;
    }

    public function charge($amount, $token)
    {
        Charge::create([
            'amount' => $amount,
            'source' => $token,
            'currency' => 'usd',
        ], $this->apiKey);
    }
}