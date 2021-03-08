<?php

declare(strict_types=1);

namespace App\CommissionTask\Iterator;

use App\CommissionTask\Model\Operation;
use App\CommissionTask\Model\Transaction;
use App\CommissionTask\Model\User;

class FileIterator implements \IteratorAggregate
{
    const COLUMN_COUNT = 6;

    private string $file;

    public function __construct(string $file)
    {
        $this->file = $file;
    }

    public function getIterator(): \Traversable
    {
        $handel = fopen($this->file, 'rb');
        if (false === $handel) {
            throw new \Exception('Error Processing');
        }

        while (false === feof($handel)) {
            $row = fgetcsv($handel);

            if (!is_array($row) || self::COLUMN_COUNT !== count($row)) {
                throw new \Exception('Not valid line');
            }

            list($date, $userId, $userType, $operationType, $operationAmount, $operationCurrency) = $row;

            yield new Transaction(
                new User($userId, $userType),
                new Operation($date, $operationType, $operationAmount, $operationCurrency, '')
            );
        }

        fclose($handel);
    }
}
