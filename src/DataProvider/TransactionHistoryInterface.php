<?php

declare(strict_types=1);

namespace App\DataProvider;

use App\Model\OperationType;
use App\Model\Transaction;

interface TransactionHistoryInterface
{
    public function addTransaction(Transaction $transaction): void;

    /**
     * @return array{0: string, 1: int} total amount in the base currency and the number of transactions
     */
    public function getTotalAmountAndTransactionCount(
        string $userId,
        OperationType $operationType,
        string $from,
        string $to
    ): array;
}
