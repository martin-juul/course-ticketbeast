<?php

namespace App;

class Reservation
{
    /** @var Ticket[] */
    private $tickets;
    /** @var string */
    private $email;

    public function __construct($tickets, string $email)
    {
        $this->tickets = $tickets;
        $this->email = $email;
    }

    public function totalCost()
    {
        return $this->tickets->sum('price');
    }

    public function tickets()
    {
        return $this->tickets;
    }

    public function cancel()
    {
        foreach($this->tickets as $ticket) {
            $ticket->release();
        }
    }

    public function email()
    {
        return $this->email;
    }
}