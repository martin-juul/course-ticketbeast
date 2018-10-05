<?php

namespace App;

class Charge
{
    private $data;

    public function __construct(array $data)
    {
        $this->data = $data;
    }

    public function amount()
    {
        return $this->data['amount'];
    }

    public function cardLastFour()
    {
        return $this->data['card_last_four'];
    }
}
