<?php

declare(strict_types=1);

namespace App\CommissionTask\Commission;

interface RangeStrategyDataProviderInterface
{
    /**
     * @return array[string $amount, int $count]
     */
    public function getTotalAmountAndTransactionCount(string $userId, string $operationType, string $from, string $to): array;
}
