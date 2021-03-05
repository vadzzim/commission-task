<?php

declare(strict_types=1);

namespace App\CommissionTask\DataProvider;

interface RateInterface
{
    public function getRates(): array;
}
