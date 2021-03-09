<?php

declare(strict_types=1);

namespace App\DataProvider;

interface RateInterface
{
    public function getRates(): array;
}
