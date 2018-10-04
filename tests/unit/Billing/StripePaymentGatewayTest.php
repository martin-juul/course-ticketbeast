<?php

use App\Billing\PaymentFailedException;
use App\Billing\PaymentGateway;
use App\Billing\StripePaymentGateway;
use Illuminate\Foundation\Testing\WithoutMiddleware;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Foundation\Testing\DatabaseTransactions;

/**
 * @group integration
 */
class StripePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTest;

    private $lastCharge;

    protected function getPaymentGateway(): PaymentGateway
    {
        return new StripePaymentGateway(config('services.stripe.secret'));
    }
}
