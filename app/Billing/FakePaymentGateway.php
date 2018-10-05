<?php

namespace App\Billing;

use App\Charge;

/**
 * Class FakePaymentGateway
 * @package App\Billing
 */
class FakePaymentGateway implements PaymentGateway
{
    public const TEST_CARD_NUMBER = '4242424242424242';

    private $charges;
    private $tokens;
    private $beforeFirstChargeCallback;

    public function __construct()
    {
        $this->charges = collect();
        $this->tokens = collect();
    }

    /**
     * @param string $cardNumber
     * @return string
     */
    public function getValidTestToken($cardNumber = self::TEST_CARD_NUMBER)
    {
        $token = 'fake-tok_' . str_random(24);
        $this->tokens[$token] = $cardNumber;

        return $token;
    }

    /**
     * @param $amount
     * @param $token
     * @return Charge
     */
    public function charge($amount, $token)
    {
        if ($this->beforeFirstChargeCallback !== null) {
            $callback = $this->beforeFirstChargeCallback;
            $this->beforeFirstChargeCallback = null;

            $callback($this);
        }

        if (!$this->tokens->has($token)) {
            throw new PaymentFailedException;
        }

        return $this->charges[] = new Charge([
            'amount' => $amount,
            'card_last_four' => substr($this->tokens[$token], -4),
        ]);
    }

    /**
     * @param callable $callback
     * @return mixed
     */
    public function newChargesDuring(callable $callback)
    {
        $chargesFrom = $this->charges->count();
        $callback($this);
        return $this->charges->slice($chargesFrom)->reverse()->values();
    }

    /**
     * @return mixed
     */
    public function totalCharges()
    {
        return $this->charges->map->amount()->sum();
    }

    /**
     * @param $callback
     * @return void
     */
    public function beforeFirstCharge(callable $callback): void
    {
        $this->beforeFirstChargeCallback = $callback;
    }
}
