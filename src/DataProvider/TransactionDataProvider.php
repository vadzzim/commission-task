<?php

declare(strict_types=1);

namespace App\DataProvider;

use App\Model\Transaction;

class TransactionDataProvider implements RangeStrategyDataProviderInterface
{
    private int $scale;

    public function __construct(int $scale)
    {
        $this->scale = $scale;
    }

    private array $storage = [];

    public function getTotalAmountAndTransactionCount(
        string $userId,
        string $operationType,
        string $from,
        string $to
    ): array {
        $filtered = array_filter($this->storage, function (Transaction $transaction) use (
            $userId,
            $operationType,
            $from,
            $to
        ) {
            $user = $transaction->user;
            $operation = $transaction->operation;

            return
                $user->id === $userId
                && $operation->type === $operationType
                && $operation->date >= $from
                && $operation->date <= $to
            ;
        });

        $sum = '0.00';
        foreach ($filtered as $transaction) {
            $operation = $transaction->operation;
            $sum = bcadd($sum, bcdiv($operation->amount, $operation->rate, $this->scale), $this->scale);
        }

        return [$sum, count($filtered)];
    }

    public function addTransaction(Transaction $transaction): void
    {
        $this->storage[] = $transaction;
    }
}
