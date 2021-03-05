<?php

declare(strict_types=1);

namespace App\CommissionTask\DataProvider;

class FixedRateDataProvider implements RateInterface
{
    public function getRates(): array
    {
        return [
            'JPY' => 129.53,
            'USD' => 1.1497,
        ];
    }
}
