<?php

declare(strict_types=1);

namespace App\DataProvider;

use App\Model\OperationType;
use App\Model\Transaction;

class TransactionDataProvider implements TransactionHistoryInterface
{
    private int $scale;

    /**
     * Totals in the base currency, bucketed by "<userId>|<operationType>" and
     * then by date, so a lookup never walks unrelated history.
     *
     * @var array<string, array<string, array{0: string, 1: int}>>
     */
    private array $storage = [];

    public function __construct(int $bcmathScale)
    {
        $this->scale = $bcmathScale;
    }

    public function addTransaction(Transaction $transaction): void
    {
        $operation = $transaction->getOperation();
        $key = self::key($transaction->getUser()->getId(), $operation->getType());
        $date = $operation->getDate();

        [$sum, $count] = $this->storage[$key][$date] ?? ['0.00', 0];
        $amount = bcdiv($operation->getAmount()->getValue(), $transaction->getRate(), $this->scale);

        $this->storage[$key][$date] = [bcadd($sum, $amount, $this->scale), $count + 1];
    }

    public function getTotalAmountAndTransactionCount(
        string $userId,
        OperationType $operationType,
        string $from,
        string $to
    ): array {
        $sum = '0.00';
        $count = 0;

        foreach ($this->storage[self::key($userId, $operationType)] ?? [] as $date => [$dailySum, $dailyCount]) {
            if ($date >= $from && $date <= $to) {
                $sum = bcadd($sum, $dailySum, $this->scale);
                $count += $dailyCount;
            }
        }

        return [$sum, $count];
    }

    private static function key(string $userId, OperationType $operationType): string
    {
        return $userId.'|'.$operationType->getValue();
    }
}
