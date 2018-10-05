<?php

namespace App\Generators;

use App\Ticket;

interface TicketCodeGenerator
{
    public function generateFor(Ticket $ticket);
}
