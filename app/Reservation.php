<?php

namespace App;

use App\Billing\PaymentGateway;

class Reservation
{
    private $tickets;
    private $email;

    public function __construct($tickets, $email)
    {
        $this->tickets = $tickets;
        $this->email = $email;
    }

    public function complete(PaymentGateway $paymentGateway, $paymentToken)
    {
        $charge = $paymentGateway->charge($this->totalCost(), $paymentToken);

        return Order::forTickets($this->tickets(), $this->email(), $charge);
    }

    public function totalCost()
    {
        return $this->tickets->sum('price');
    }

    public function tickets()
    {
        return $this->tickets;
    }

    public function email()
    {
        return $this->email;
    }

    public function cancel()
    {
        foreach ($this->tickets as $ticket) {
            $ticket->release();
        }
    }
}
