<?php

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;

class FakePaymentGatewayTest extends TestCase
{
    use PaymentGatewayContractTest;

    /** @test */
    public function running_a_hook_before_the_first_charge()
    {
        $paymentGateway = new FakePaymentGateway;
        $timesCallbackRan = 0;

        $paymentGateway->beforeFirstCharge(function ($paymentGateway) use (&$timesCallbackRan) {
            $timesCallbackRan++;
            $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
            $this->assertEquals(2500, $paymentGateway->totalCharges());
        });

        $paymentGateway->charge(2500, $paymentGateway->getValidTestToken());
        $this->assertEquals(1, $timesCallbackRan);
        $this->assertEquals(5000, $paymentGateway->totalCharges());
    }

    protected function getPaymentGateway(): PaymentGateway
    {
        return new FakePaymentGateway();
    }
}
