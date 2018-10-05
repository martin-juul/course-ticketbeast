<?php

namespace App\Facades;

use App\Generators\OrderConfirmationNumberGenerator;
use Illuminate\Support\Facades\Facade;

class OrderConfirmationNumber extends Facade
{
    /**
     * @return string
     */
    protected static function getFacadeAccessor()
    {
        return OrderConfirmationNumberGenerator::class;
    }
}
