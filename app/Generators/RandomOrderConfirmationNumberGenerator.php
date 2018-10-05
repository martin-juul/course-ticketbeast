<?php

namespace App\Generators;

class RandomOrderConfirmationNumberGenerator implements OrderConfirmationNumberGenerator
{
    public function generate()
    {
        $pool = 'ABCDEFGHJKLMNPQRSTUVWXYZ23456789';
        $length = 24;

        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }
}
