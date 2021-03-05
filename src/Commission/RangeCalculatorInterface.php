<?php

declare(strict_types=1);

namespace App\CommissionTask\Commission;

interface RangeCalculatorInterface
{
    public function getRange(string $date): array;
}
