<?php

declare(strict_types=1);

namespace App\Commission;

interface RangeCalculatorInterface
{
    public function getRange(string $date): array;
}
