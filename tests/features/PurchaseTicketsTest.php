<?php

use App\Billing\FakePaymentGateway;
use App\Billing\PaymentGateway;
use App\Concert;
use App\Facades\OrderConfirmationNumber;
use App\Mail\OrderConfirmationEmail;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Support\Facades\Mail;

class PurchaseTicketsTest extends TestCase
{
    use DatabaseMigrations;

    /** @var PaymentGateway */
    private $paymentGateway;

    /** @test */
    public function customer_can_purchase_tickets_to_a_published_concert(): void
    {
        $this->disableExceptionHandling();
        Mail::fake();

        OrderConfirmationNumber::shouldReceive('generate')->andReturn('ORDERCONFIRMATION1234');
        \App\Facades\TicketCode::shouldReceive('generateFor')->andReturn('TICKETCODE1', 'TICKETCODE2', 'TICKETCODE3');

        $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 3250])->addTickets(3);

        $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(201);

        $this->seeJsonSubset([
            'confirmation_number' => 'ORDERCONFIRMATION1234',
            'email' => 'john@example.com',
            'amount' => 9750,
            'tickets' => [
                ['code' => 'TICKETCODE1'],
                ['code' => 'TICKETCODE2'],
                ['code' => 'TICKETCODE3'],
            ],
        ]);

        $this->assertEquals(9750, $this->paymentGateway->totalCharges());
        $this->assertTrue($concert->hasOrderFor('john@example.com'));

        $order = $concert->ordersFor('john@example.com')->first();
        $this->assertEquals(3, $order->ticketQuantity());
        Mail::assertSent(OrderConfirmationEmail::class, function ($mail) use ($order) {
            return $mail->hasTo('john@example.com')
                && $mail->order->id === $order->id;
        });
    }

    private function orderTickets($concert, $params)
    {
        $savedRequest = $this->app['request'];
        $this->response = $this->json('POST', "/concerts/{$concert->id}/orders", $params);
        $this->app['request'] = $savedRequest;
    }

    private function assertResponseStatus($status)
    {
        $this->response->assertStatus($status);
    }

    private function seeJsonSubset($data)
    {
        $this->response->assertJson($data);
    }

    /** @test */
    public function cannot_purchase_tickets_to_an_unpublished_concert(): void
    {
        $concert = factory(Concert::class)->states('unpublished')->create()->addTickets(3);

        $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(404);
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
    }

    /** @test */
    public function an_order_is_not_created_if_payment_fails(): void
    {
        $concert = factory(Concert::class)->states('published')->create(['ticket_price' => 3250])->addTickets(3);

        $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
            'payment_token' => 'invalid-payment-token',
        ]);

        $this->assertResponseStatus(422);
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(3, $concert->ticketsRemaining());
    }

    /** @test */
    public function cannot_purchase_more_tickets_than_remain(): void
    {
        $concert = factory(Concert::class)->states('published')->create()->addTickets(50);

        $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 51,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertResponseStatus(422);
        $this->assertFalse($concert->hasOrderFor('john@example.com'));
        $this->assertEquals(0, $this->paymentGateway->totalCharges());
        $this->assertEquals(50, $concert->ticketsRemaining());
    }

    /** @test */
    public function cannot_purchase_tickets_another_customer_is_already_trying_to_purchase(): void
    {
        $this->disableExceptionHandling();

        $concert = factory(Concert::class)->states('published')->create([
            'ticket_price' => 1200
        ])->addTickets(3);

        $this->paymentGateway->beforeFirstCharge(function ($paymentGateway) use ($concert) {
            $this->orderTickets($concert, [
                'email' => 'personB@example.com',
                'ticket_quantity' => 1,
                'payment_token' => $this->paymentGateway->getValidTestToken(),
            ]);

            $this->assertResponseStatus(422);
            $this->assertFalse($concert->hasOrderFor('personB@example.com'));
            $this->assertEquals(0, $this->paymentGateway->totalCharges());
        });

        $this->orderTickets($concert, [
            'email' => 'personA@example.com',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertEquals(3600, $this->paymentGateway->totalCharges());
        $this->assertTrue($concert->hasOrderFor('personA@example.com'));
        $this->assertEquals(3, $concert->ordersFor('personA@example.com')->first()->ticketQuantity());
    }

    /** @test */
    public function email_is_required_to_purchase_tickets(): void
    {
        $concert = factory(Concert::class)->states('published')->create();

        $this->orderTickets($concert, [
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('email');
    }

    private function assertValidationError($field)
    {
        $this->assertResponseStatus(422);
        $this->assertArrayHasKey($field, $this->decodeResponseJson());
    }

    private function decodeResponseJson()
    {
        return $this->response->decodeResponseJson();
    }

    /** @test */
    public function email_must_be_valid_to_purchase_tickets(): void
    {
        $concert = factory(Concert::class)->states('published')->create();

        $this->orderTickets($concert, [
            'email' => 'not-an-email-address',
            'ticket_quantity' => 3,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('email');
    }

    /** @test */
    public function ticket_quantity_is_required_to_purchase_tickets(): void
    {
        $concert = factory(Concert::class)->states('published')->create();

        $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('ticket_quantity');
    }

    /** @test */
    public function ticket_quantity_must_be_at_least_1_to_purchase_tickets(): void
    {
        $concert = factory(Concert::class)->states('published')->create();

        $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 0,
            'payment_token' => $this->paymentGateway->getValidTestToken(),
        ]);

        $this->assertValidationError('ticket_quantity');
    }

    /** @test */
    public function payment_token_is_required(): void
    {
        $concert = factory(Concert::class)->states('published')->create();

        $this->orderTickets($concert, [
            'email' => 'john@example.com',
            'ticket_quantity' => 3,
        ]);

        $this->assertValidationError('payment_token');
    }

    protected function setUp(): void
    {
        parent::setUp();
        $this->paymentGateway = new FakePaymentGateway;
        $this->app->instance(PaymentGateway::class, $this->paymentGateway);
    }
}
