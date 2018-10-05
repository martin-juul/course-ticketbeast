<?php

namespace App\Generators;

use App\Ticket;
use Hashids\Hashids;

class HashidsTicketCodeGenerator implements TicketCodeGenerator
{
    private $hashids;

    public function __construct(string $salt)
    {
        $this->hashids = new Hashids($salt, 6, 'ABCDEFGHIJKLMNOPQRSTUVWXYZ');
    }

    public function generateFor(Ticket $ticket)
    {
        return $this->hashids->encode($ticket->id);
    }
}
