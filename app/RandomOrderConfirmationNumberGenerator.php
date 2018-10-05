<?php

namespace App;

class RandomOrderConfirmationNumberGenerator implements OrderConfirmationNumberGenerator
{

    public function generate()
    {
        $pool = 'ABCDEFGHJKLMNOPQRSTUVWXYZ23456789';
        $length = 24;

        return substr(str_shuffle(str_repeat($pool, $length)), 0, $length);
    }
}
