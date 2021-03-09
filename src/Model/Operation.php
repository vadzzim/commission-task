<?php

declare(strict_types=1);

namespace App\Model;

class Operation
{
    public string $date;
    public string $type;
    public string $amount;
    public string $currency;
    public string $rate;

    public function __construct(string $date, string $type, string $amount, string $currency, string $rate)
    {
        $this->date = $date;
        $this->type = $type;
        $this->amount = $amount;
        $this->currency = $currency;
        $this->rate = $rate;
    }
}
