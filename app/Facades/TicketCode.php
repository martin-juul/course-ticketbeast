<?php

namespace App\Facades;

use App\Generators\TicketCodeGenerator;
use Illuminate\Support\Facades\Facade;

class TicketCode extends Facade
{
    protected static function getMockableClass()
    {
        return static::getFacadeAccessor();
    }

    protected static function getFacadeAccessor()
    {
        return TicketCodeGenerator::class;
    }
}
