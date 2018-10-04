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
    private $lastCharge;

    private function lastCharge()
    {
        return \Stripe\Charge::all([
            'limit' => 1,
        ], ['api_key' => config('services.stripe.secret')])['data'][0];
    }

    private function newCharges()
    {
        return \Stripe\Charge::all([
            'limit' => 1,
            'ending_before' => $this->lastCharge->id,
        ], ['api_key' => config('services.stripe.secret')])['data'];
    }

    protected function setUp()
    {
        parent::setUp();
        $this->lastCharge = $this->lastCharge();
    }

    protected function getPaymentGateway(): PaymentGateway
    {
        return new StripePaymentGateway(config('services.stripe.secret'));
    }

    /** @test */
    public function charges_with_a_valid_payment_token_are_successful()
    {
        $paymentGateway = $this->getPaymentGateway();

        $newCharges = $paymentGateway->newChargesDuring(function ($paymentGateway) {
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
        });

        $this->assertCount(1, $newCharges);
        $this->assertEquals(2500, $newCharges->sum());
    }

    /**
     * @test
     */
    public function charges_with_an_invalid_payment_token_fail()
    {
        try {
            $paymentGateway = new StripePaymentGateway(config('services.stripe.secret'));
            $paymentGateway->charge(2500, 'invalid-payment-token');
        } catch (PaymentFailedException $e) {
            $this->assertCount(0, $this->newCharges());
            return;
        }

        $this->fail('Charging with an invalid payment token did not throw a PaymentFailedException');

//        $paymentGateway = new StripePaymentGateway(config('services.stripe.secret'));
//        $result = $paymentGateway->charge(2500, 'invalid-payment-token');
//        $this->assertFalse($result);
    }
}
