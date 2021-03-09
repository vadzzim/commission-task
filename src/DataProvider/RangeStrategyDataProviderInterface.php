<?php

declare(strict_types=1);

namespace App\DataProvider;

interface RangeStrategyDataProviderInterface
{
    public function getTotalAmountAndTransactionCount(string $userId, string $operationType, string $from, string $to): array;
}
